<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id', 'student_name', 'student_phone', 'status', 'is_ai_active', 'last_activity_at'
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'is_ai_active' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
