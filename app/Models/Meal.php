<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function foods()
    {
        return $this->belongsToMany(Food::class)
            ->withPivot('quantity_grams');
    }

    /**
     * Calculates the total calories for all ingredients in the meal.
     */
    public function getTotalCaloriesAttribute(): int
    {
        // This requires the 'foods' relationship to be loaded.
        if (!$this->relationLoaded('foods')) {
            return 0;
        }

        return round($this->foods->reduce(function ($carry, $food) {
            $caloriesPerGram = $food->calories / 100;
            $ingredientCalories = $caloriesPerGram * $food->pivot->quantity_grams;
            return $carry + $ingredientCalories;
        }, 0));
    }
}