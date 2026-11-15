<?php

namespace App\Services;

use App\Models\LearningTrack;
use App\Models\MediaItem;
use App\Models\Unit;

class LessonGroupResolver
{
    /**
     * Resolve consecutive units belonging to the same parent lesson / video clip
     * without requiring any database schema changes.
     *
     * @return array{
     *     units: array<int, Unit>,
     *     unit_ids: array<int, int>,
     *     lead_video: array<string, mixed>|null,
     *     previous_group_unit: Unit|null,
     *     next_group_unit: Unit|null,
     * }
     */
    public function resolveForUnit(Unit $unit, ?LearningTrack $currentTrack = null): array
    {
        $orderedUnits = $this->loadOrderedUnits($unit, $currentTrack);

        if (empty($orderedUnits)) {
            $orderedUnits = [$unit];
        }

        // Partition consecutive units into clip-based groups.
        $groups = [];
        $currentGroup = [];
        $activeSignature = null;

        foreach ($orderedUnits as $candidate) {
            $sig = $this->extractVideoSignature($candidate);

            if ($sig !== null && $activeSignature !== null && $sig !== $activeSignature) {
                if (!empty($currentGroup)) {
                    $groups[] = $currentGroup;
                }
                $currentGroup = [$candidate];
                $activeSignature = $sig;
            } else {
                $currentGroup[] = $candidate;
                if ($activeSignature === null && $sig !== null) {
                    $activeSignature = $sig;
                }
            }
        }

        if (!empty($currentGroup)) {
            $groups[] = $currentGroup;
        }

        $targetGroupIndex = 0;
        foreach ($groups as $idx => $grp) {
            foreach ($grp as $u) {
                if ((int) $u->id === (int) $unit->id) {
                    $targetGroupIndex = $idx;
                    break 2;
                }
            }
        }

        $activeGroupUnits = $groups[$targetGroupIndex] ?? [$unit];
        $prevGroup = $targetGroupIndex > 0 ? $groups[$targetGroupIndex - 1] : null;
        $nextGroup = $targetGroupIndex < count($groups) - 1 ? $groups[$targetGroupIndex + 1] : null;

        return [
            'units' => $activeGroupUnits,
            'unit_ids' => array_map(fn (Unit $u) => (int) $u->id, $activeGroupUnits),
            'lead_video' => $this->extractLeadVideo($activeGroupUnits),
            'previous_group_unit' => $prevGroup ? $prevGroup[0] : null,
            'next_group_unit' => $nextGroup ? $nextGroup[0] : null,
        ];
    }

    /**
     * @return array<int, Unit>
     */
    protected function loadOrderedUnits(Unit $unit, ?LearningTrack $currentTrack): array
    {
        if ($currentTrack && $unit->trackUnits()->where('learning_track_id', $currentTrack->id)->exists()) {
            return $currentTrack->trackUnits()
                ->with('unit')
                ->orderBy('step_number')
                ->get()
                ->pluck('unit')
                ->filter(fn ($u) => $u instanceof Unit && $u->is_published)
                ->values()
                ->all();
        }

        if ($unit->course_session_id) {
            return Unit::where('course_session_id', $unit->course_session_id)
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->all();
        }

        return [$unit];
    }

    protected function extractVideoSignature(Unit $unit): ?string
    {
        if (!empty($unit->video_id)) {
            return 'unit_video:' . trim((string) $unit->video_id);
        }

        $blocks = is_array($unit->content_blocks) ? $unit->content_blocks : [];
        foreach ($blocks as $block) {
            if (!is_array($block) || ($block['type'] ?? '') !== 'video') {
                continue;
            }
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            if (!empty($data['bunny_id'])) {
                return 'bunny:' . trim((string) $data['bunny_id']);
            }
            if (!empty($data['media_item_id'])) {
                return 'media:' . (int) $data['media_item_id'];
            }
            if (!empty($data['video_path'])) {
                return 'path:' . trim((string) $data['video_path']);
            }
            if (!empty($data['custom_url'])) {
                return 'url:' . trim((string) $data['custom_url']);
            }
        }

        return null;
    }

    /**
     * @param  array<int, Unit>  $units
     * @return array<string, mixed>|null
     */
    protected function extractLeadVideo(array $units): ?array
    {
        foreach ($units as $u) {
            if (!empty($u->video_id)) {
                return [
                    'video_id' => $u->video_id,
                    'video_url' => null,
                    'target_sentence' => trim((string) ($u->transcript ?? '')),
                    'instructions' => null,
                    'meaning_malayalam' => null,
                ];
            }

            $blocks = is_array($u->content_blocks) ? $u->content_blocks : [];
            foreach ($blocks as $block) {
                if (!is_array($block) || ($block['type'] ?? '') !== 'video') {
                    continue;
                }
                $data = is_array($block['data'] ?? null) ? $block['data'] : [];
                $rawVideo = $data['video_path'] ?? $data['custom_url'] ?? null;
                if (!$rawVideo && !empty($data['media_item_id'])) {
                    $media = MediaItem::find($data['media_item_id']);
                    $rawVideo = $media?->path;
                }
                $videoUrl = $this->resolveAssetUrl($rawVideo);
                if (!empty($videoUrl) || !empty($data['bunny_id'])) {
                    return [
                        'video_id' => $data['bunny_id'] ?? null,
                        'video_url' => $videoUrl,
                        'target_sentence' => trim((string) ($data['transcript'] ?? $u->transcript ?? '')),
                        'instructions' => $data['instructions'] ?? null,
                        'meaning_malayalam' => $data['meaning_malayalam'] ?? null,
                    ];
                }
            }
        }

        return null;
    }

    protected function resolveAssetUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        $trimmed = trim($path);
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }
        $clean = ltrim($trimmed, '/');
        if (str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }
        return asset('storage/' . $clean);
    }
}
