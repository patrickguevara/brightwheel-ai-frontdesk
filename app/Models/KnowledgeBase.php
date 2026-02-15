<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBase extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'knowledge_base';

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_active' => 'boolean',
            'is_seasonal' => 'boolean',
            'effective_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    protected $fillable = [
        'category',
        'title',
        'content',
        'keywords',
        'is_active',
        'is_seasonal',
        'effective_date',
        'expiry_date',
        'updated_by',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
