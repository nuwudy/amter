<?php

namespace Tests\Unit;

use App\Models\Unit;
use App\Services\LessonChatTransformer;
use Tests\TestCase;

class LessonChatTransformerTest extends TestCase
{
    public function test_transforms_blocks_into_grouped_tutor_and_student_chat_turns(): void
    {
        $unit = new Unit([
            'title' => '1',
            'video_id' => null,
            'audio_url' => null,
            'transcript' => 'Good morning everyone',
        ]);

        $blocks = [
            [
                'type' => 'rich_text',
                'data' => ['content' => '<p>Welcome to lesson 1</p>', 'heading' => 'Intro'],
            ],
            [
                'type' => 'video',
                'data' => [
                    'resolved_video_url' => 'https://amter.in/storage/videos/intro.mp4',
                    'instructions' => 'Watch this clip',
                ],
            ],
            [
                'type' => 'listen_speak',
                'data' => [
                    'english_text' => 'How are you doing?',
                    'malayalam_text' => 'സുഖമാണോ?',
                    'hide_english' => false,
                ],
            ],
            [
                'type' => 'quiz',
                'data' => [
                    'question' => 'Choose the greeting',
                    'options' => ['Hello', 'Chair'],
                    'correct_index' => 0,
                ],
            ],
        ];

        $transformer = new LessonChatTransformer();
        $turns = $transformer->transform($unit, $blocks, 'https://amter.in/tts-stream');

        $this->assertCount(6, $turns);

        $this->assertSame('tutor', $turns[0]['role']);
        $this->assertSame('text', $turns[0]['kind']);

        $this->assertSame('tutor', $turns[1]['role']);
        $this->assertSame('video', $turns[1]['kind']);
        $this->assertSame(5, $turns[1]['data']['loop_preview_seconds']);

        $this->assertSame('tutor', $turns[2]['role']);
        $this->assertSame('tts', $turns[2]['kind']);
        $this->assertStringContainsString('How%20are%20you%20doing%3F', $turns[2]['data']['tts_url']);

        $this->assertSame('student', $turns[3]['role']);
        $this->assertSame('voice_match', $turns[3]['kind']);
        $this->assertSame(['How are you doing?'], $turns[3]['data']['phrases']);

        $this->assertSame('student', $turns[4]['role']);
        $this->assertSame('quiz', $turns[4]['kind']);

        $this->assertSame('student', $turns[5]['role']);
        $this->assertSame('voice_match', $turns[5]['kind']);
        $this->assertSame(['Good morning everyone'], $turns[5]['data']['phrases']);
    }
}
