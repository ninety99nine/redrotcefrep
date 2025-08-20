<?php

namespace App\Services;

use App\Models\AiMessage;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Auth;

class OpenAiService
{
    /**
     * Prompt.
     *
     * @param string $userContent
     * @param array $messages
     * @param string $aiAssistantId
     * @param string|null $aiLessonId
     * @param AiMessage|null $aiMessage
     * @return AiMessage
     * @throws \OpenAI\Exceptions\ErrorException
     */
    public function prompt(string $userContent, array $messages = [], string $aiAssistantId, string|null $aiLessonId = null, AiMessage|null $aiMessage = null): AiMessage
    {
        $requestAt = now();
        $messages = count($messages) ? $messages : [
            [
                'role' => 'system',
                'content' => 'You are an assistant.',
            ]
        ];

        $response = OpenAI::chat()->create([
            'model' => config('openai.model'),
            'max_tokens' => (int) config('openai.max_tokens'),
            'temperature' => (float) config('openai.temperature'),
            'messages' => [
                ...$messages,
                [
                    'role' => 'user',
                    'content' => $userContent,
                ],
            ],
        ]);

        $totalTokens = $response->usage->totalTokens;
        $promptTokens = $response->usage->promptTokens;
        $content = $response->choices[0]->message->content;
        $completionTokens = $response->usage->completionTokens;

        $data = [
            'response_at' => now(),
            'request_at' => $requestAt,
            'user_id' => Auth::user()->id,
            'ai_lesson_id' => $aiLessonId,
            'user_content' => $userContent,
            'total_tokens' => $totalTokens,
            'assistant_content' => $content,
            'prompt_tokens' => $promptTokens,
            'ai_assistant_id' => $aiAssistantId,
            'completion_tokens' => $completionTokens,
        ];

        if($aiMessage) {
            $aiMessage->update($data);
        }else{
            $aiMessage = AiMessage::create($data);
        }

        return $aiMessage;
    }
}
