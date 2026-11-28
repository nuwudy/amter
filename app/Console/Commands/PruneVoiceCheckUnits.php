<?php

namespace App\Console\Commands;

use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneVoiceCheckUnits extends Command
{
    protected $signature = 'units:prune-voice-check
        {--session= : Only process units in a specific course_session_id}
        {--force : Actually delete pure voice_match units and strip voice_match blocks from mixed units}';

    protected $description = 'Delete standalone Voice Check (voice_match) units and strip voice_match blocks from mixed units.';

    /**
     * Block types that carry independent lesson content worth preserving.
     */
    private const CORE_BLOCK_TYPES = [
        'video',
        'audio',
        'text_to_speech',
        'listen_speak',
        'dictation_test',
        'quiz',
        'code',
    ];

    public function handle(): int
    {
        $sessionId = $this->option('session');
        $force = (bool) $this->option('force');

        $query = Unit::query()
            ->orderBy('course_session_id')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($sessionId !== null && $sessionId !== '') {
            $query->where('course_session_id', (int) $sessionId);
        }

        $toDelete = [];
        $toStrip = [];

        foreach ($query->cursor() as $unit) {
            $blocks = is_array($unit->content_blocks) ? $unit->content_blocks : [];
            if (empty($blocks)) {
                continue;
            }

            $hasVoiceMatch = false;
            $hasCoreBlock = !empty($unit->video_id) || !empty($unit->audio_url);

            foreach ($blocks as $block) {
                if (!is_array($block)) {
                    continue;
                }
                $type = (string) ($block['type'] ?? '');
                if ($type === 'voice_match') {
                    $hasVoiceMatch = true;
                } elseif (in_array($type, self::CORE_BLOCK_TYPES, true)) {
                    $hasCoreBlock = true;
                }
            }

            if (!$hasVoiceMatch) {
                continue;
            }

            if ($hasCoreBlock) {
                $toStrip[] = $unit;
            } else {
                $toDelete[] = $unit;
            }
        }

        $this->info(($force ? '[EXECUTION]' : '[DRY RUN]') . ' Voice Check cleanup summary:');
        $this->line('  - Pure Voice Check units to DELETE: ' . count($toDelete));
        $this->line('  - Mixed units to STRIP voice_match block only: ' . count($toStrip));

        if (!empty($toDelete)) {
            $sample = collect($toDelete)->take(15)->map(
                fn (Unit $u) => "#{$u->id} (session {$u->course_session_id}, {$u->title})"
            )->implode(', ');
            $this->line('  - Sample delete targets: ' . $sample . (count($toDelete) > 15 ? ' ...' : ''));
        }

        if (!empty($toStrip)) {
            $sample = collect($toStrip)->take(10)->map(
                fn (Unit $u) => "#{$u->id} (session {$u->course_session_id}, {$u->title})"
            )->implode(', ');
            $this->line('  - Sample strip targets: ' . $sample . (count($toStrip) > 10 ? ' ...' : ''));
        }

        if (!$force) {
            $this->warn('Dry run complete. No database changes were made. Re-run with --force to apply.');
            return self::SUCCESS;
        }

        $affectedSessions = [];

        DB::transaction(function () use ($toDelete, $toStrip, &$affectedSessions) {
            foreach ($toStrip as $unit) {
                $blocks = is_array($unit->content_blocks) ? $unit->content_blocks : [];
                $filtered = array_values(array_filter(
                    $blocks,
                    fn ($b) => !is_array($b) || (($b['type'] ?? '') !== 'voice_match')
                ));
                $unit->content_blocks = $filtered;
                $unit->saveQuietly();
            }

            $deleteIds = array_map(fn (Unit $u) => (int) $u->id, $toDelete);
            foreach ($toDelete as $unit) {
                if ($unit->course_session_id) {
                    $affectedSessions[(int) $unit->course_session_id] = true;
                }
            }

            if (!empty($deleteIds)) {
                foreach (array_chunk($deleteIds, 500) as $chunk) {
                    Unit::whereIn('id', $chunk)->delete();
                }
            }
        });

        foreach (array_keys($affectedSessions) as $sid) {
            Unit::resequenceSession((int) $sid);
        }

        $this->info('Cleanup complete! Deleted ' . count($toDelete) . ' units, stripped ' . count($toStrip) . ' mixed units, and resequenced ' . count($affectedSessions) . ' session(s).');

        return self::SUCCESS;
    }
}
