<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QueueService extends Model
{
    protected $fillable = ['name', 'code', 'description', 'color', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'service_id');
    }

    public function todayQueues(): HasMany
    {
        return $this->hasMany(Queue::class, 'service_id')
            ->whereDate('queue_date', today());
    }

    public function waitingCount(): int
    {
        return $this->todayQueues()->where('status', 'waiting')->count();
    }

    public function getNextSequence(): int
    {
        $last = $this->queues()
            ->whereDate('queue_date', today())
            ->max('sequence');
        return ($last ?? 0) + 1;
    }
}
