<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    protected $fillable = [
        'queue_number', 'patient_name', 'phone', 'service_id',
        'counter_id', 'status', 'reject_reason', 'called_at', 'served_at', 'done_at',
        'sequence', 'queue_date', 'document_number', 'file_path',
    ];

    protected $casts = [
        'called_at'  => 'datetime',
        'served_at'  => 'datetime',
        'done_at'    => 'datetime',
        'queue_date' => 'date',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(QueueService::class, 'service_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(QueueCounter::class, 'counter_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Diajukan',
            'admin'   => 'Diproses',
            'done'    => 'Selesai',
            'picked_up'=> 'Selesai & Diambil',
            'rejected'=> 'Ditolak',
            default   => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'waiting',
            'admin'   => 'calling',
            'done'    => 'done',
            'picked_up'=> 'done', // you can reuse done or another color like success
            'rejected'=> 'danger',
            default   => 'secondary',
        };
    }
}
