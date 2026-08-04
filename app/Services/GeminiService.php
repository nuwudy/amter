<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function generateResponse(string $prompt, array $history = [])
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API Key is missing.');
            return "I am sorry, but my brain connection is missing (API Key not found).";
        }

        // Format history for Gemini if needed. 
        // For simple usage, we might just append previous turn.
        // Gemini expects "role": "user" | "model"
        
        $contents = [];
        
        // Add history
        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'ai' ? 'model' : 'user',
                'parts' => [['text' => $msg['message']]]
            ];
        }

        // Add current prompt
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 150, // Keep responses concise for voice
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? "I didn't understand that.";
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                return "I am having trouble thinking right now. Please try again.";
            }
        } catch (\Exception $e) {
            Log::error('Gemini Connection Error: ' . $e->getMessage());
            return "Connection to AI failed.";
        }
    }
}
