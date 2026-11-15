<?php

namespace App\Services;

use App\Models\MediaItem;
use App\Models\Unit;

class LessonChatTransformer
{
    /**
     * Transform a Unit and its normalized content blocks into grouped chat turns
     * for both the Web Guided Tutor Chat UI and the Flutter native chat player.
     *
     * @param  Unit  $unit
     * @param  array<int, array<string, mixed>>  $normalizedBlocks
     * @param  string  $ttsStreamEndpoint
     * @return array<int, array<string, mixed>>
     */
    public function transform(Unit $unit, array $normalizedBlocks, string $ttsStreamEndpoint): array
    {
        $turns = [];
        $index = 1;

        $pushTurn = function (string $role, string $kind, array $data, ?string $title = null) use (&$turns, &$index): void {
            $turns[] = [
                'id' => 'turn-' . $index++,
                'role' => $role,
                'kind' => $kind,
                'title' => $title,
                'data' => $data,
            ];
        };

        if (!empty($unit->video_id)) {
            $pushTurn('tutor', 'video', [
                'video_id' => $unit->video_id,
                'video_url' => null,
                'instructions' => null,
                'transcript' => null,
                'loop_preview_seconds' => 5,
            ], $unit->title);
        }

        if (!empty($unit->audio_url)) {
            $pushTurn('tutor', 'audio', [
                'audio_url' => $this->resolveAssetUrl($unit->audio_url),
                'transcript' => null,
            ], 'Lesson Audio');
        }

        foreach ($normalizedBlocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? 'rich_text');
            $data = is_array($block['data'] ?? null) ? $this->normalizeBlockData($type, $block['data']) : [];
            $title = $this->extractTitle($data);

            switch ($type) {
                case 'video':
                    $videoUrl = $data['resolved_video_url'] ?? null;
                    if (!empty($videoUrl)) {
                        $pushTurn('tutor', 'video', [
                            'video_id' => null,
                            'video_url' => $videoUrl,
                            'instructions' => $data['instructions'] ?? null,
                            'transcript' => $data['transcript'] ?? null,
                            'loop_preview_seconds' => 5,
                        ], $title);
                    }
                    break;

                case 'audio':
                    $audioUrl = $data['resolved_audio_url'] ?? null;
                    if (!empty($audioUrl)) {
                        $pushTurn('tutor', 'audio', [
                            'audio_url' => $audioUrl,
                            'transcript' => $data['transcript'] ?? null,
                        ], $title ?: 'Audio Clip');
                    }
                    break;

                case 'image':
                    $imageUrl = $data['resolved_image_url'] ?? null;
                    if (!empty($imageUrl)) {
                        $pushTurn('tutor', 'image', [
                            'image_url' => $imageUrl,
                            'caption' => $data['caption'] ?? $title,
                        ], $title);
                    }
                    break;

                case 'rich_text':
                    $html = (string) ($data['content'] ?? $data['body'] ?? $data['text'] ?? '');
                    if (trim($html) !== '') {
                        $pushTurn('tutor', 'text', [
                            'html' => $html,
                            'alignment' => $data['alignment'] ?? 'left',
                            'text_color' => $data['text_color'] ?? 'default',
                        ], $title);
                    }
                    break;

                case 'text_to_speech':
                    $text = trim((string) ($data['text'] ?? $data['body'] ?? ''));
                    if ($text !== '') {
                        $pushTurn('tutor', 'tts', [
                            'text' => $text,
                            'heading' => $data['heading'] ?? null,
                            'show_headings' => (bool) ($data['show_headings'] ?? false),
                            'instructions' => $data['instructions'] ?? null,
                            'meaning_malayalam' => $data['meaning_malayalam'] ?? null,
                            'hide_english' => (bool) ($data['hide_english'] ?? false),
                            'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $text),
                        ], $data['heading'] ?? null);

                        if (!empty($data['student_input_allowed'])) {
                            $pushTurn('student', 'voice_match', [
                                'phrases' => [$text],
                                'instructions' => 'Repeat what the tutor said',
                                'meaning_malayalam' => $data['meaning_malayalam'] ?? null,
                                'button_label' => 'Tap to Speak & Match',
                                'hide_english' => (bool) ($data['hide_english'] ?? false),
                                'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $text),
                            ], 'Your Turn');
                        }
                    }
                    break;

                case 'listen_speak':
                    $englishText = trim((string) ($data['english_text'] ?? $data['text'] ?? ''));
                    $malayalamText = $data['malayalam_text'] ?? null;
                    if ($englishText !== '') {
                        $pushTurn('tutor', 'tts', [
                            'text' => $englishText,
                            'heading' => 'Listen',
                            'show_headings' => true,
                            'instructions' => $data['instructions'] ?? null,
                            'meaning_malayalam' => $malayalamText,
                            'hide_english' => (bool) ($data['hide_english'] ?? false),
                            'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $englishText),
                        ], 'Listen');

                        $pushTurn('student', 'voice_match', [
                            'phrases' => [$englishText],
                            'instructions' => $data['instructions'] ?? 'Now repeat the phrase aloud',
                            'meaning_malayalam' => $malayalamText,
                            'button_label' => 'Tap to Speak & Match',
                            'hide_english' => (bool) ($data['hide_english'] ?? false),
                            'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $englishText),
                        ], 'Speak');
                    }
                    break;

                case 'voice_match':
                    $phrases = $this->extractPhrases($data['phrase'] ?? null);
                    if (!empty($phrases)) {
                        $pushTurn('student', 'voice_match', [
                            'phrases' => $phrases,
                            'instructions' => $data['instructions'] ?? null,
                            'meaning_malayalam' => $data['meaning_malayalam'] ?? null,
                            'button_label' => !empty(trim((string) ($data['button_label'] ?? '')))
                                ? (string) $data['button_label']
                                : 'Tap to Speak & Match',
                            'hide_english' => (bool) ($data['hide_english'] ?? false),
                            'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $phrases[0]),
                        ], 'Voice Practice');
                    }
                    break;

                case 'dictation_test':
                    $text = trim((string) ($data['correct_sentence'] ?? $data['text'] ?? $data['target_text'] ?? ''));
                    if ($text !== '') {
                        $pushTurn('student', 'dictation', [
                            'heading' => $data['heading'] ?? 'Dictation & Listening Test',
                            'text' => $text,
                            'correct_sentence' => $text,
                            'instructions' => $data['instructions'] ?? 'Listen carefully and type what you hear.',
                            'success_message' => $data['success_message'] ?? 'Great job! Your listening is spot on!',
                            'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $text),
                        ], 'Dictation');
                    }
                    break;

                case 'quiz':
                    $question = trim((string) ($data['question'] ?? ''));
                    $options = is_array($data['options'] ?? null) ? array_values($data['options']) : [];
                    if ($question !== '' && !empty($options)) {
                        $pushTurn('student', 'quiz', [
                            'question' => $question,
                            'options' => $options,
                            'instructions' => $data['instructions'] ?? null,
                            'correct_index' => (int) ($data['correct_index'] ?? $data['correct_answer'] ?? 0),
                            'explanation' => $data['explanation'] ?? null,
                        ], 'Quick Check');
                    }
                    break;

                case 'code':
                    $code = (string) ($data['code'] ?? '');
                    if (trim($code) !== '') {
                        $pushTurn('tutor', 'code', [
                            'code' => $code,
                            'language' => $data['language'] ?? 'text',
                        ], $title);
                    }
                    break;

                case 'separator':
                    $pushTurn('tutor', 'separator', [
                        'label' => $data['label'] ?? null,
                    ]);
                    break;
            }
        }

        if (!empty($unit->transcript) && trim((string) $unit->transcript) !== '') {
            $transcript = trim((string) $unit->transcript);
            $pushTurn('student', 'voice_match', [
                'phrases' => [$transcript],
                'instructions' => 'Practice reading the lesson transcript aloud.',
                'meaning_malayalam' => null,
                'button_label' => 'Practice Transcript',
                'hide_english' => false,
                'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $transcript),
            ], 'Transcript & Voice Practice');
        }

        return $turns;
    }

    /**
     * Transform a group of consecutive units sharing a parent lesson/video clip
     * into a unified Guided Tutor Chat stream with deduplicated artwork/Malayalam
     * guidance and Before/After baseline + mastery tags.
     *
     * @param  array<int, Unit>  $units
     * @param  string  $ttsStreamEndpoint
     * @param  array<string, mixed>|null  $leadVideo
     * @return array<int, array<string, mixed>>
     */
    public function transformGroup(array $units, string $ttsStreamEndpoint, ?array $leadVideo = null): array
    {
        $turns = [];
        $index = 1;
        $seenIntroArtwork = false;
        $seenMalayalamIntro = false;
        $seenImageUrls = [];
        $seenDrillPhrases = [];
        $pendingImageUrl = null;
        $firstVoiceTagged = false;
        $lastVoiceTurnIndex = null;

        $normalizePhrase = static fn (string $p): string => strtolower(trim(preg_replace('/[^a-z0-9\s]/i', '', $p)));

        $pushTurn = function (string $role, string $kind, array $data, ?string $title = null) use (&$turns, &$index, &$firstVoiceTagged, &$lastVoiceTurnIndex, &$pendingImageUrl, &$seenDrillPhrases, $normalizePhrase): void {
            if ($pendingImageUrl !== null && in_array($kind, ['tts', 'tts_drill', 'voice_match', 'text'], true) && empty($data['is_malayalam_intro'])) {
                $data['attached_image_url'] = $pendingImageUrl;
                $pendingImageUrl = null;
            }
            if ($kind === 'voice_match' || $kind === 'tts_drill') {
                if (!$firstVoiceTagged) {
                    $data['is_baseline'] = true;
                    $firstVoiceTagged = true;
                }
                $lastVoiceTurnIndex = count($turns);
                foreach ((array) ($data['phrases'] ?? []) as $p) {
                    $norm = $normalizePhrase((string) $p);
                    if ($norm !== '') {
                        $seenDrillPhrases[$norm] = true;
                    }
                }
            }
            $turns[] = [
                'id' => 'turn-' . $index++,
                'role' => $role,
                'kind' => $kind,
                'title' => $title,
                'data' => $data,
            ];
        };

        $fullTargetSentence = trim((string) ($leadVideo['target_sentence'] ?? ''));
        $leadMalayalam = trim((string) ($leadVideo['meaning_malayalam'] ?? ''));

        if ($leadMalayalam !== '') {
            $seenMalayalamIntro = true;
            $pushTurn('tutor', 'text', [
                'html' => '<p>' . e($leadMalayalam) . '</p>',
                'is_malayalam_intro' => true,
            ], 'Malayalam Guidance');
        }

        if ($fullTargetSentence !== '') {
            $pushTurn('student', 'voice_match', [
                'phrases' => [$fullTargetSentence],
                'instructions' => 'Attempt #1: Try saying the full sentence before we break it down.',
                'meaning_malayalam' => $leadMalayalam !== '' ? $leadMalayalam : null,
                'button_label' => 'Record Attempt #1',
                'hide_english' => false,
                'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $fullTargetSentence),
            ], 'Baseline Attempt');
        }

        foreach ($units as $unit) {
            $blocks = is_array($unit->content_blocks) ? $unit->content_blocks : [];
            foreach ($blocks as $block) {
                if (!is_array($block)) {
                    continue;
                }
                $type = (string) ($block['type'] ?? 'rich_text');
                $rawData = is_array($block['data'] ?? null) ? $block['data'] : [];
                $data = $this->normalizeBlockData($type, $rawData);
                $title = $this->extractTitle($data);

                // Skip duplicate video turns; the lead video is docked in the sticky header.
                if ($type === 'video') {
                    continue;
                }

                if ($type === 'image') {
                    $imageUrl = $data['resolved_image_url'] ?? null;
                    if (empty($imageUrl)) {
                        continue;
                    }
                    if (!$seenIntroArtwork) {
                        $seenIntroArtwork = true;
                        $seenImageUrls[$imageUrl] = true;
                        $pendingImageUrl = $imageUrl;
                    } elseif (!isset($seenImageUrls[$imageUrl])) {
                        $seenImageUrls[$imageUrl] = true;
                        $pendingImageUrl = $imageUrl;
                    }
                    continue;
                }

                if ($type === 'rich_text') {
                    $html = trim((string) ($data['content'] ?? $data['body'] ?? $data['text'] ?? ''));
                    if ($html === '') {
                        continue;
                    }
                    $plain = trim(strip_tags($html));
                    $hasMalayalam = (bool) preg_match('/[\x{0D00}-\x{0D7F}]/u', $plain);
                    if ($hasMalayalam && $seenMalayalamIntro) {
                        // Deduplicate repetitive Malayalam instruction blocks across grouped units.
                        continue;
                    }
                    if ($hasMalayalam) {
                        $seenMalayalamIntro = true;
                    }
                    $pushTurn('tutor', 'text', [
                        'html' => $html,
                        'alignment' => $data['alignment'] ?? 'left',
                        'text_color' => $data['text_color'] ?? 'default',
                    ], $title);
                    continue;
                }

                if ($type === 'audio') {
                    $audioUrl = $data['resolved_audio_url'] ?? null;
                    if (!empty($audioUrl)) {
                        $pushTurn('tutor', 'audio', [
                            'audio_url' => $audioUrl,
                            'transcript' => $data['transcript'] ?? null,
                        ], $title ?: 'Audio Clip');
                    }
                    continue;
                }

                if ($type === 'text_to_speech') {
                    $text = trim((string) ($data['text'] ?? $data['body'] ?? ''));
                    if ($text !== '') {
                        // Every listening unit automatically becomes a Listen -> Tap to Speak chat drill.
                        $pushTurn('tutor', 'tts_drill', [
                            'text' => $text,
                            'phrases' => [$text],
                            'heading' => $data['heading'] ?? null,
                            'show_headings' => (bool) ($data['show_headings'] ?? false),
                            'instructions' => $data['instructions'] ?? null,
                            'meaning_malayalam' => $data['meaning_malayalam'] ?? null,
                            'hide_english' => (bool) ($data['hide_english'] ?? false),
                            'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $text),
                        ], $data['heading'] ?? 'Listen & Speak');
                    }
                    continue;
                }

                if ($type === 'listen_speak') {
                    $englishText = trim((string) ($data['english_text'] ?? $data['text'] ?? ''));
                    $malayalamText = $data['malayalam_text'] ?? null;
                    if ($englishText !== '') {
                        $pushTurn('tutor', 'tts_drill', [
                            'text' => $englishText,
                            'phrases' => [$englishText],
                            'heading' => 'Listen & Speak',
                            'instructions' => $data['instructions'] ?? null,
                            'meaning_malayalam' => $malayalamText,
                            'hide_english' => (bool) ($data['hide_english'] ?? false),
                            'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $englishText),
                        ], 'Listen & Speak');
                    }
                    continue;
                }

                if ($type === 'voice_match') {
                    $phrases = $this->extractPhrases($data['phrase'] ?? null);
                    if (!empty($phrases)) {
                        // Automatically absorb/skip existing voice_match units already covered by a listening drill.
                        $norm = $normalizePhrase($phrases[0]);
                        if ($norm !== '' && isset($seenDrillPhrases[$norm])) {
                            continue;
                        }

                        $pushTurn('tutor', 'tts_drill', [
                            'text' => $phrases[0],
                            'phrases' => $phrases,
                            'instructions' => $data['instructions'] ?? null,
                            'meaning_malayalam' => $data['meaning_malayalam'] ?? null,
                            'hide_english' => (bool) ($data['hide_english'] ?? false),
                            'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $phrases[0]),
                        ], 'Listen & Speak');
                    }
                    continue;
                }

                if ($type === 'dictation_test') {
                    $text = trim((string) ($data['correct_sentence'] ?? $data['text'] ?? $data['target_text'] ?? ''));
                    if ($text !== '') {
                        $pushTurn('student', 'dictation', [
                            'heading' => $data['heading'] ?? 'Dictation & Listening Test',
                            'text' => $text,
                            'correct_sentence' => $text,
                            'instructions' => $data['instructions'] ?? 'Listen carefully and type what you hear.',
                            'success_message' => $data['success_message'] ?? 'Great job! Your listening is spot on!',
                            'tts_url' => $this->buildTtsUrl($ttsStreamEndpoint, $text),
                        ], 'Dictation');
                    }
                    continue;
                }

                if ($type === 'quiz') {
                    $question = trim((string) ($data['question'] ?? ''));
                    $options = is_array($data['options'] ?? null) ? array_values($data['options']) : [];
                    if ($question !== '' && !empty($options)) {
                        $pushTurn('student', 'quiz', [
                            'question' => $question,
                            'options' => $options,
                            'instructions' => $data['instructions'] ?? null,
                            'correct_index' => (int) ($data['correct_index'] ?? $data['correct_answer'] ?? 0),
                            'explanation' => $data['explanation'] ?? null,
                        ], 'Quick Check');
                    }
                }
            }
        }

        if ($lastVoiceTurnIndex !== null && isset($turns[$lastVoiceTurnIndex])) {
            $turns[$lastVoiceTurnIndex]['data']['is_mastery'] = true;
        }

        return $turns;
    }

    protected function normalizeBlockData(string $type, array $data): array
    {
        if ($type === 'image' && empty($data['resolved_image_url'])) {
            $cUrl = $data['custom_url'] ?? null;
            if (!empty($data['media_item_selection'])) {
                $media = MediaItem::find($data['media_item_selection']);
                if ($media) {
                    $cUrl = $media->path;
                }
            }
            if (!empty($cUrl) && !str_contains($cUrl, '/') && !str_starts_with($cUrl, 'http')) {
                $cUrl = 'media-library/' . $cUrl;
            }
            $rawPath = $cUrl ?: ($data['url'] ?? null);
            $data['resolved_image_url'] = $this->resolveAssetUrl($rawPath);
        } elseif ($type === 'video' && empty($data['resolved_video_url'])) {
            $rawVideo = $data['video_path'] ?? $data['custom_url'] ?? null;
            if (!$rawVideo && !empty($data['media_item_id'])) {
                $media = MediaItem::find($data['media_item_id']);
                $rawVideo = $media?->path;
            }
            $data['resolved_video_url'] = $this->resolveAssetUrl($rawVideo);
        } elseif ($type === 'audio' && empty($data['resolved_audio_url'])) {
            $rawAudio = $data['custom_url'] ?? $data['url'] ?? null;
            $data['resolved_audio_url'] = $this->resolveAssetUrl($rawAudio);
        }

        return $data;
    }

    protected function extractTitle(array $data): ?string
    {
        $raw = $data['heading'] ?? $data['title'] ?? $data['audio_title'] ?? $data['speaker'] ?? null;
        if ($raw === null) {
            return null;
        }
        $str = trim((string) $raw);
        return $str !== '' ? $str : null;
    }

    /**
     * @param  mixed  $rawPhrase
     * @return array<int, string>
     */
    protected function extractPhrases(mixed $rawPhrase): array
    {
        if (is_array($rawPhrase)) {
            return array_values(array_filter(array_map(
                fn ($p) => trim((string) $p),
                $rawPhrase
            ), fn ($p) => $p !== ''));
        }

        if (is_string($rawPhrase)) {
            $trimmed = trim($rawPhrase);
            if ($trimmed === '') {
                return [];
            }
            if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    return array_values(array_filter(array_map(
                        fn ($p) => trim((string) $p),
                        $decoded
                    ), fn ($p) => $p !== ''));
                }
                $inner = trim(substr($trimmed, 1, -1));
                return $inner !== '' ? [$inner] : [];
            }
            return [$trimmed];
        }

        return [];
    }

    protected function buildTtsUrl(string $endpoint, string $text): string
    {
        $base = rtrim($endpoint, '/');
        return $base . '?text=' . rawurlencode($text) . '&tl=en-US';
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
