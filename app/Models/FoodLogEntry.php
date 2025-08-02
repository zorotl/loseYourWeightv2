<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodLogEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'calories',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'consumed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}