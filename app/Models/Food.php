<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'foods';

    protected $fillable = [
        'name',
        'brand',
        'calories',
        'protein',
        'carbohydrates',
        'fat',
        'source',
        'source_id',
        'creator_id',
    ];

    /**
     * The user who manually created this food item.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * The log entries associated with this food.
     */
    public function logEntries()
    {
        return $this->hasMany(FoodLogEntry::class);
    }

    /**
     * The meals that this food is associated with.
     */
    public function meals()
    {
        return $this->belongsToMany(Meal::class)
            ->withPivot('quantity_grams');
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'food_user_favorites');
    }
}