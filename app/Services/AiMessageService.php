<?php

namespace App\Services;

use stdClass;
use Exception;
use Carbon\Carbon;
use App\Jobs\SendSms;
use App\Models\AiLesson;
use App\Models\AiMessage;
use App\Enums\Association;
use App\Models\AiAssistant;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use App\Models\AiAssistantTokenUsage;
use App\Http\Resources\AiMessageResource;
use App\Http\Resources\AiMessageResources;

class AiMessageService extends BaseService
{
    /**
     * Show AI messages.
     *
     * @param array $data
     * @return AiMessageResources|array
     */
    public function showAiMessages(array $data): AiMessageResources|array
    {
        $userId = $data['user_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = AiMessage::query();
        }else {
            $query = AiMessage::whereUserId($userId ?? Auth::user()->id);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create AI message.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createAiMessage(array $data): array
    {
        /** @var User $user */
        $user =  Auth::user();
        $aiAssistant = AiAssistant::with(['activeSubscription'])->withCount(['subscriptions'])->whereUserId($user->id)->first();

        if(!$aiAssistant) $aiAssistant = $user->aiAssistant()->create();
        $usageEligibility = $this->assessUsageEligibility($aiAssistant);

        if(!$usageEligibility->proceed) return [
            'created' => false,
            'message' => $usageEligibility->message,
            'can_top_up' => $usageEligibility->can_top_up,
            'can_subscribe' => $usageEligibility->can_subscribe
        ];

        try {

            $aiMessage = $this->promptAssistant($data, $aiAssistant);


        } catch (\OpenAI\Exceptions\ErrorException $th) {

            if($th->getErrorType() == 'insufficient_quota') {
                return [
                    'created' => false,
                    'message' => 'We’re currently unable to provide a response due to Quota limitations on our service. We are working to resolve this soon'
                ];
            }else{
                return [
                    'created' => false,
                    'message' => $th->getMessage()
                ];
            }
        }

        if((new PlatformService)->isSms() && $user->mobile_number) {

            SendSms::dispatch(
                $aiMessage->assistant_content,
                $user->mobile_number->formatE164()
            );

        }

        $quota = $this->deductTokensAndUpdateUsage($aiAssistant, $aiMessage, $usageEligibility);

        return [
            'created' => true,
            'quota' => $quota,
            'ai_message' => $aiMessage,
            'message' => $usageEligibility->message,
            'can_top_up' => $usageEligibility->can_top_up,
            'can_subscribe' => $usageEligibility->can_subscribe,
            'subscription' => [
                'subscription_end_at' => $aiAssistant->activeSubscription?->end_at
            ]
        ];
    }

    /**
     * Delete AI messages.
     *
     * @param array $aiMessageIds
     * @return array
     * @throws Exception
     */
    public function deleteAiMessages(array $aiMessageIds): array
    {
        $aiMessages = AiMessage::whereIn('id', $aiMessageIds)->get();

        if ($totalAiMessages = $aiMessages->count()) {

            foreach ($aiMessages as $aiMessage) {

                $this->deleteAiMessage($aiMessage);

            }

            return ['message' => $totalAiMessages . ($totalAiMessages == 1 ? ' AI Message' : ' AI Messages') . ' deleted'];
        } else {
            throw new Exception('No AI Messages deleted');
        }
    }

    /**
     * Show AI message.
     *
     * @param AiMessage $aiMessage
     * @return AiMessageResource
     */
    public function showAiMessage(AiMessage $aiMessage): AiMessageResource
    {
        return $this->showResource($aiMessage);
    }

    /**
     * Update AI message.
     *
     * @param AiMessage $aiMessage
     * @param array $data
     * @return array
     */
    public function updateAiMessage(AiMessage $aiMessage, array $data): array
    {
        $aiAssistant = $aiMessage->aiAssistant;
        $usageEligibility = $this->assessUsageEligibility($aiAssistant);

        if(!$usageEligibility->proceed) return [
            'updated' => false,
            'message' => $usageEligibility->message,
            'can_top_up' => $usageEligibility->can_top_up,
            'can_subscribe' => $usageEligibility->can_subscribe
        ];

        try {

            $aiMessage = $this->promptAssistant($data, $aiAssistant, $aiMessage);

        } catch (\OpenAI\Exceptions\ErrorException $th) {

            if($th->getErrorType() == 'insufficient_quota') {
                return ['updated' => false, 'message' => 'We’re currently unable to provide a response due to quota limitations on our service. We are working to resolve this soon'];
            }

        }

        $quota = $this->deductTokensAndUpdateUsage($aiAssistant, $aiMessage, $usageEligibility);

        if(!$this->checkIfHasRelationOnRequest('aiAssistant')) $aiMessage->unsetRelation('aiAssistant');

        return [
            'updated' => true,
            'quota' => $quota,
            'ai_message' => $aiMessage,
            'message' => $usageEligibility->message,
            'can_top_up' => $usageEligibility->can_top_up,
            'can_subscribe' => $usageEligibility->can_subscribe,
            'subscription' => [
                'subscription_end_at' => $aiAssistant->activeSubscription?->end_at
            ]
        ];
    }

    /**
     * Delete AI message.
     *
     * @param AiMessage $aiMessage
     * @return array
     * @throws Exception
     */
    public function deleteAiMessage(AiMessage $aiMessage): array
    {
        $deleted = $aiMessage->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'AI message deleted' : 'AI message delete unsuccessful'
        ];
    }

    /**
     * Assess usage eligibility.
     *
     * @param AiAssistant $aiAssistant
     * @return stdClass
     */
    public function assessUsageEligibility(AiAssistant $aiAssistant): stdClass
    {
        $response = new stdClass;
        $response->message = null;
        $response->proceed = false;
        $response->can_top_up = false;
        $response->can_subscribe = false;
        $response->use_free_tokens = false;
        $response->use_paid_tokens = false;
        $response->use_paid_top_up_tokens = false;

        $activeSubscription = $aiAssistant->activeSubscription;
        $hasRemainingFreeTokens = $aiAssistant->remaining_free_tokens > 0;

        if(!$hasRemainingFreeTokens) {
            if($activeSubscription) {

                $hasRemainingTokenUsageToDate = $this->hasRemainingTokenUsageToDate($aiAssistant);

                if(!$hasRemainingTokenUsageToDate) {

                    $hasRemainingPaidTopUpTokens = $aiAssistant->remaining_paid_top_up_tokens > 0;

                    if(!$hasRemainingPaidTopUpTokens) {

                        $response->can_top_up = true;

                        if($this->hasRemainingSubscribedDays($aiAssistant)) {
                            $date = now()->copy()->addDay()->startOfDay();
                            $response->message = 'You have reached your daily limit. Please top up to continue or come back in ' . $this->timeLeftToDate($date);
                        }else{
                            $date = $activeSubscription->end_at->copy();
                            $response->message = 'You have reached your daily limit. Please top up to continue or subscribe again in ' . $this->timeLeftToDate($date);
                        }

                    }else{
                        $response->proceed = true;
                        $response->use_paid_top_up_tokens = true;
                    }

                }else{
                    $response->proceed = true;
                    $response->use_paid_tokens = true;
                }

            }else{

                $response->can_subscribe = true;
                $hasNeverSubscribed = $aiAssistant->subscriptions_count == 0;

                if($hasNeverSubscribed) {
                    $response->message = 'You do not have a subscription, please subscribe.';
                }else{
                    $response->message = 'Your subscription has ended. Please subscribe to continue.';
                }

            }
        }else{
            $response->proceed = true;
            $response->use_free_tokens = true;
        }

        return $response;
    }

    /**
     * Has remaining token usage to date.
     *
     * @param AiAssistant $aiAssistant
     * @return bool
     */
    private function hasRemainingTokenUsageToDate(AiAssistant $aiAssistant): bool
    {
        $activeSubscription = $aiAssistant->activeSubscription;

        $tokensPerDay = $aiAssistant->total_paid_tokens / $this->totalSubscribedDays($activeSubscription);
        $maxAllowedTokenUsageToDate = $tokensPerDay * $this->totalUsedDays($activeSubscription);

        $dailyUsage = AiAssistantTokenUsage::where('ai_assistant_id', $aiAssistant->id)
                                            ->where('created_at', '>=', $activeSubscription->start_at)
                                            ->where('created_at', '<=', now())->get();

        $totalUsedTokensToDate = collect($dailyUsage)->sum(function ($record) {
            return $record['paid_tokens_used'];
        });

        $remainingTokenUsageToDate = $maxAllowedTokenUsageToDate - $totalUsedTokensToDate;

        return $remainingTokenUsageToDate > 0;
    }

    /**
     * Total subscribed days.
     *
     * @param Subscription $activeSubscription
     * @return int
     */
    private function totalSubscribedDays(Subscription $activeSubscription): int
    {
        return Carbon::parse($activeSubscription->start_at)->diffInDays($activeSubscription->end_at) ?? 1;
    }

    /**
     * Total used days.
     *
     * @param Subscription $activeSubscription
     * @return int
     */
    private function totalUsedDays(Subscription $activeSubscription): int
    {
        return Carbon::parse($activeSubscription->start_at->startOfDay())->diffInDays(now()->startOfDay()->addDays(1));
    }

    /**
     * Has remaining subscribed days.
     *
     * @param AiAssistant $aiAssistant
     * @return bool
     */
    private function hasRemainingSubscribedDays(AiAssistant $aiAssistant): bool
    {
        $activeSubscription = $aiAssistant->activeSubscription;

        $totalRemainingDays = $this->totalSubscribedDays($activeSubscription) - $this->totalUsedDays($activeSubscription);
        return $totalRemainingDays > 0;
    }

    /**
     * Time left to date.
     *
     * @param Carbon $date
     * @return string
     */
    private function timeLeftToDate(Carbon $date): string
    {
        $hoursUntilLater = now()->diffInHours($date);
        $minutesUntilLater = now()->diffInMinutes($date);
        $secondsUntilLater = now()->diffInSeconds($date);

        if($hoursUntilLater > 0) {
            return $hoursUntilLater . ($hoursUntilLater == 1 ? ' hour' : ' hours');
        }else if($minutesUntilLater > 0) {
            return $minutesUntilLater . ($minutesUntilLater == 1 ? ' minute' : ' minutes');
        }else {
            return $secondsUntilLater . ($secondsUntilLater == 1 ? ' seconds' : ' seconds');
        }
    }

    /**
     * Prompt assistant.
     *
     * @param array $data
     * @param AiAssistant $aiAssistant
     * @param AiMessage|null $aiMessage
     * @return AiMessage
     * @throws \OpenAI\Exceptions\ErrorException
     */
    private function promptAssistant(array $data, AiAssistant $aiAssistant, AiMessage|null $aiMessage = null): AiMessage
    {
        $aiLesson = isset($data['ai_lesson_id']) ? AiLesson::with(['aiTopic'])->find($data['ai_lesson_id']): null;

        $userContent =  $aiLesson?->prompt ?? $data['user_content'];
        $messages = [$this->getSystemMessage($aiLesson), ...$this->getPreviousMessages()];

        return (new OpenAiService)->prompt($userContent, $messages, $aiAssistant->id, $aiLesson?->id, $aiMessage);
    }

    /**
     * Get system message for OpenAI Chat API.
     *
     * @param AiLesson|null $aiLesson
     * @return array
     */
    private function getSystemMessage(?AiLesson $aiLesson): array
    {
        // Default system prompt
        $defaultPrompt = 'You are Perfect Assistant, an expert consultant assisting businesses in Botswana. Provide clear, accurate, concise and practical responses tailored to the user’s query.';

        // Use lesson-specific prompt if available, otherwise default
        $content = $aiLesson?->aiTopic?->system_prompt ?? $defaultPrompt;

        // Platform-specific formatting instructions
        $platformInstructions = [
            'ussd' => 'Responses are served on USSD. Don\'t use numbering e.g "1. ". Don\'t use lists. Use only one paragraph (not multiline content). Keep it short, direct, and under 800 characters.',
            'sms' => 'Responses are served on SMS. Don\'t use numbering e.g "1. ". Don\'t use lists. Use only one paragraph (not multiline content). Keep it short, direct, and under 300 characters.',
            'web' => 'Responses are served on a web platform. Use structured responses with paragraphs for clarity, and keep responses detailed but concise.',
        ];

        $platformService = (new PlatformService);

        // Determine platform and append instructions
        if ($platformService->isUssd()) {
            $content .= ' ' . $platformInstructions['ussd'];
        } elseif ($platformService->isSms()) {
            $content .= ' ' . $platformInstructions['sms'];
        } else {
            $content .= ' ' . $platformInstructions['web'];
        }

        return [
            'role' => 'system',
            'content' => trim($content)
        ];
    }

    /**
     * Get previous messages.
     *
     * @return array
     */
    private function getPreviousMessages(): array
    {
        $messages = [];

        /** @var User $user */
        $user =  Auth::user();
        $previousAiMessages = $user->aiMessages()->latest()->take(5)->get();

        foreach($previousAiMessages->reverse() as $previousAiMessage) {

            $userContent = $previousAiMessage->user_content;
            $assistantContent = $previousAiMessage->assistant_content;

            if (strlen($userContent) > 200) $userContent = substr($userContent, 0, 200);
            if (strlen($assistantContent) > 200) $assistantContent = substr($assistantContent, 0, 200);

            array_push($messages, [
                'role' => 'user',
                'content' => $userContent
            ]);

            array_push($messages, [
                'role' => 'assistant',
                'content' => $assistantContent
            ]);

        }

        return $messages;
    }

    /**
     * Deduct tokens and update usage.
     *
     * @param AiAssistant $aiAssistant
     * @param AiMessage $aiMessage
     * @param stdClass $usageEligibility
     * @return array
     */
    private function deductTokensAndUpdateUsage(AiAssistant $aiAssistant, AiMessage $aiMessage, stdClass $usageEligibility): array
    {
        $totalTokensUsed = $aiMessage->total_tokens;
        $remainingFreeTokensBefore = $remainingFreeTokensAfter = $aiAssistant->remaining_free_tokens;
        $remainingPaidTokensBefore = $remainingPaidTokensAfter = $aiAssistant->remaining_paid_tokens;
        $remainingPaidTopUpTokensBefore = $remainingPaidTopUpTokensAfter = $aiAssistant->remaining_paid_top_up_tokens;

        // Deduct tokens based on usage
        if ($usageEligibility->use_free_tokens) {
            $remainingFreeTokensAfter -= $totalTokensUsed;
            if ($remainingFreeTokensAfter < 0) {
                // Handle overflow to paid top_up tokens
                $remainingPaidTopUpTokensAfter += $remainingFreeTokensAfter;
                $remainingFreeTokensAfter = 0;
                if ($remainingPaidTopUpTokensAfter < 0) {
                    // Handle overflow to paid tokens
                    $remainingPaidTokensAfter += $remainingPaidTopUpTokensAfter;
                    $remainingPaidTopUpTokensAfter = 0;
                }
            }
        } elseif ($usageEligibility->use_paid_top_up_tokens) {
            $remainingPaidTopUpTokensAfter -= $totalTokensUsed;
            if ($remainingPaidTopUpTokensAfter < 0) {
                // Handle overflow to paid tokens
                $remainingPaidTokensAfter += $remainingPaidTopUpTokensAfter;
                $remainingPaidTopUpTokensAfter = 0;
            }
        } elseif ($usageEligibility->use_paid_tokens) {
            $remainingPaidTokensAfter -= $totalTokensUsed;
        }

        // Update the AI Assistant's token balances
        $aiAssistant->update([
            'remaining_free_tokens' => $remainingFreeTokensAfter,
            'remaining_paid_tokens' => $remainingPaidTokensAfter,
            'remaining_paid_top_up_tokens' => $remainingPaidTopUpTokensAfter
        ]);

        // Record daily usage
        AiAssistantTokenUsage::create([
            'ai_assistant_id' => $aiAssistant->id,
            'request_tokens_used' => $aiMessage->prompt_tokens,
            'response_tokens_used' => $aiMessage->completion_tokens,
            'free_tokens_used' => $remainingFreeTokensBefore - $remainingFreeTokensAfter,
            'paid_tokens_used' => $remainingPaidTokensBefore - $remainingPaidTokensAfter,
            'paid_top_up_tokens_used' => $remainingPaidTopUpTokensBefore - $remainingPaidTopUpTokensAfter
        ]);

        return [
            'total_paid_tokens' => $aiAssistant->total_paid_tokens,
            'remaining_free_tokens' => $remainingFreeTokensAfter,
            'remaining_paid_tokens' => $remainingPaidTokensAfter,
            'remaining_paid_top_up_tokens' => $remainingPaidTopUpTokensAfter,
        ];
    }
}
