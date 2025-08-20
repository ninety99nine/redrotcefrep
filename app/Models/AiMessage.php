<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiMessage extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'request_at' => 'datetime',
            'response_at' => 'datetime',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_content', 'assistant_content', 'prompt_tokens', 'completion_tokens', 'total_tokens',
        'request_at', 'response_at', 'user_id', 'ai_assistant_id', 'ai_lesson_id'
    ];

    /**
     * Scope a query by search term.
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return void
     */
    #[Scope]
    protected function search(Builder $query, string $searchTerm): void
    {
        $query->where('user_id', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get AI assistant.
     *
     * @return BelongsTo
     */
    public function aiAssistant(): BelongsTo
    {
        return $this->belongsTo(AiAssistant::class);
    }

    /**
     * Get AI lesson.
     *
     * @return BelongsTo
     */
    public function aiLesson(): BelongsTo
    {
        return $this->belongsTo(AiLesson::class);
    }
}
