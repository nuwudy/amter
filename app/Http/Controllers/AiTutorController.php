<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;

class AiTutorController extends Controller
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'array|nullable',
            'system_prompt' => 'string|nullable',
        ]);

        $userMessage = $request->input('message');
        $history = $request->input('history', []);
        $systemPrompt = $request->input('system_prompt', 'You are a helpful English tutor. Keep answers short and simple.');

        // Prepend system prompt to history logic if needed, or send as first message.
        // For Gemini, we can send system instruction specifically or just as first user message.
        // Let's prepend it as a robust "context" starter if history is empty.
        
        if (empty($history) && !empty($systemPrompt)) {
             $history[] = ['role' => 'user', 'message' => "System Instructions: " . $systemPrompt];
             $history[] = ['role' => 'ai', 'message' => "Understood. I will act as instructed."];
        }

        $response = $this->gemini->generateResponse($userMessage, $history);

        return response()->json([
            'response' => $response
        ]);
    }
}
