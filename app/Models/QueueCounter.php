<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QueueCounter extends Model
{
    protected $fillable = ['name', 'officer_name', 'is_active', 'current_queue_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function currentQueue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'current_queue_id');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'counter_id');
    }
}
