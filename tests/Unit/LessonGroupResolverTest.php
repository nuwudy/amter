<?php

namespace Tests\Unit;

use App\Models\Unit;
use App\Services\LessonChatTransformer;
use App\Services\LessonGroupResolver;
use Tests\TestCase;

class LessonGroupResolverTest extends TestCase
{
    public function test_transform_group_deduplicates_malayalam_and_artwork_and_tags_before_after(): void
    {
        $u1 = new Unit([
            'id' => 5310,
            'title' => '5310',
            'content_blocks' => [
                ['type' => 'rich_text', 'data' => ['content' => '<p>മലയാളം നിർദ്ദേശം</p>']],
                ['type' => 'image', 'data' => ['custom_url' => 'https://amter.in/storage/art/10-years.webp', 'caption' => '10 Years']],
                ['type' => 'listen_speak', 'data' => ['english_text' => 'Ten years', 'malayalam_text' => 'പത്തു വർഷം']],
            ],
        ]);

        $u2 = new Unit([
            'id' => 5311,
            'title' => '5311',
            'content_blocks' => [
                ['type' => 'rich_text', 'data' => ['content' => '<p>മലയാളം നിർദ്ദേശം വീണ്ടും</p>']],
                ['type' => 'image', 'data' => ['custom_url' => 'https://amter.in/storage/art/10-years.webp']],
                ['type' => 'image', 'data' => ['custom_url' => 'https://amter.in/storage/art/cue-coffee.webp']],
                ['type' => 'voice_match', 'data' => ['phrase' => ['I have been here for five years'], 'hide_english' => true]],
            ],
        ]);

        $transformer = new LessonChatTransformer();
        $turns = $transformer->transformGroup(
            [$u1, $u2],
            'https://amter.in/tts-stream',
            [
                'video_url' => 'https://amter.in/storage/videos/ml01.mp4',
                'target_sentence' => 'I have been here for ten years',
                'meaning_malayalam' => 'ഞാൻ പത്തു വർഷമായി ഇവിടെയുണ്ട്',
            ]
        );

        // Inline image attached to the turn that follows it, instead of floating standalone image bubbles
        $turnsWithImages = array_values(array_filter($turns, fn ($t) => !empty($t['data']['attached_image_url'])));
        $this->assertCount(2, $turnsWithImages);
        $this->assertSame('https://amter.in/storage/art/10-years.webp', $turnsWithImages[0]['data']['attached_image_url']);
        $this->assertSame('https://amter.in/storage/art/cue-coffee.webp', $turnsWithImages[1]['data']['attached_image_url']);

        // Malayalam rich_text blocks in u1 and u2 were deduplicated because leadMalayalam was already emitted
        $textTurns = array_values(array_filter($turns, fn ($t) => $t['kind'] === 'text'));
        $this->assertCount(1, $textTurns);
        $this->assertTrue($textTurns[0]['data']['is_malayalam_intro']);

        // Baseline & Mastery tags across voice_match / merged tts_drill turns
        $voiceTurns = array_values(array_filter($turns, fn ($t) => in_array($t['kind'], ['voice_match', 'tts_drill'], true)));
        $this->assertTrue($voiceTurns[0]['data']['is_baseline'] ?? false);
        $this->assertTrue($voiceTurns[count($voiceTurns) - 1]['data']['is_mastery'] ?? false);
    }

    public function test_resolver_partitions_consecutive_units_by_video_signature(): void
    {
        $resolver = new class extends LessonGroupResolver {
            public function resolveWithMockUnits(array $units, Unit $target): array
            {
                $groups = [];
                $currentGroup = [];
                $activeSig = null;

                foreach ($units as $candidate) {
                    $sig = $this->extractVideoSignature($candidate);
                    if ($sig !== null && $activeSig !== null && $sig !== $activeSig) {
                        $groups[] = $currentGroup;
                        $currentGroup = [$candidate];
                        $activeSig = $sig;
                    } else {
                        $currentGroup[] = $candidate;
                        if ($activeSig === null && $sig !== null) {
                            $activeSig = $sig;
                        }
                    }
                }
                if (!empty($currentGroup)) {
                    $groups[] = $currentGroup;
                }

                return $groups;
            }
        };

        $u1 = new Unit(['id' => 1, 'content_blocks' => [['type' => 'video', 'data' => ['custom_url' => 'ml01.mp4']]]]);
        $u2 = new Unit(['id' => 2, 'content_blocks' => [['type' => 'listen_speak', 'data' => ['english_text' => 'Hi']]]]);
        $u3 = new Unit(['id' => 3, 'content_blocks' => [['type' => 'video', 'data' => ['custom_url' => 'ml02.mp4']]]]);

        $groups = $resolver->resolveWithMockUnits([$u1, $u2, $u3], $u1);
        $this->assertCount(2, $groups);
        $this->assertCount(2, $groups[0]); // u1 + u2
        $this->assertCount(1, $groups[1]); // u3
    }
}
