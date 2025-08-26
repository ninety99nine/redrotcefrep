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

        $enabled = config('openai.enabled', false);

        if($enabled) {

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
            $completionTokens = $response->usage->completionTokens;
            $content = $this->cleanText($response->choices[0]->message->content);

        }else{

            $totalTokens = 20;
            $promptTokens = 10;
            $completionTokens = 10;
            $content = 'This is a test response from our AI Assistant';

        }

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
            'completion_tokens' => $completionTokens
        ];

        if($aiMessage) {
            $aiMessage->update($data);
        }else{
            $aiMessage = AiMessage::create($data);
        }

        return $aiMessage;
    }

    /**
     * Clean hidden characters from a string.
     *
     * @param string $text
     * @return string
     */
    function cleanText(string $text): string
    {
        // Normalize non-breaking spaces and soft hyphens
        $text = str_replace(["\xC2\xA0", "\xC2\xAD"], ' ', $text);

        // Remove zero-width and invisible Unicode chars
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);

        // Remove uncommon Unicode spaces (en quad to hair space, line/paragraph sep)
        $text = preg_replace('/[\x{2000}-\x{200A}\x{2028}\x{2029}]/u', ' ', $text);

        // Remove other control characters (except line breaks and tabs)
        $text = preg_replace('/[[:cntrl:]](?<!\r)(?<!\n)(?<!\t)/', '', $text);

        // Collapse multiple spaces
        $text = preg_replace('/ {2,}/', ' ', $text);

        // Trim spaces at start and end
        return trim($text);
    }
}
