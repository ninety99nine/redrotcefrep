<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiAssistantTokenUsage extends Model
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
            'free_tokens_used' => 'integer',
            'paid_tokens_used' => 'integer',
            'request_tokens_used' => 'integer',
            'response_tokens_used' => 'integer',
            'paid_top_up_tokens_used' => 'integer',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_tokens_used','response_tokens_used','free_tokens_used','paid_tokens_used',
        'paid_top_up_tokens_used','ai_assistant_id'
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
        $query->where('ai_assistant_id', 'like', '%' . $searchTerm . '%');
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
}
