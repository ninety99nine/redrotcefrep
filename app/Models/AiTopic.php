<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiTopic extends Model
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
            'visible' => 'boolean'
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'system_prompt', 'visible', 'position'
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
        $query->where('name', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get AI lessons.
     *
     * @return HasMany
     */
    public function aiLessons(): HasMany
    {
        return $this->hasMany(AiLesson::class);
    }

    /**
     * Get AI messages.
     *
     * @return HasMany
     */
    public function aiMessages(): HasMany
    {
        return $this->hasMany(AiMessage::class);
    }
}
