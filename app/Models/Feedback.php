<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'priority',
        'message',
        'status',
        'url_at_submission',
        'user_agent',
        'admin_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}