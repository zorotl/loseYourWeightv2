<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'height_cm',
        'date_of_birth',
        'gender',
        'activity_level',
        'target_weight_kg',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * The activity level multipliers for TDEE calculation.
     * Eine Sammlung magischer Zahlen, die irgendjemand mal für klug hielt.
     */
    private const ACTIVITY_MULTIPLIERS = [
        1 => 1.2,    // Sitzend
        2 => 1.375,  // Leicht aktiv
        3 => 1.55,   // Mässig aktiv
        4 => 1.725,  // Sehr aktiv
        5 => 1.9,    // Extrem aktiv
    ];

    /**
     * Calculates the user's age. Weil Mathe Spass macht.
     */
    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

    /**
     * Calculates the Basal Metabolic Rate (BMR) using Mifflin-St Jeor.
     * Das ist die Energie, die man verbraucht, wenn man nur rumliegt und auf den Tod wartet.
     */
    public function getBmrAttribute(): float
    {
        if (!$this->height_cm || !$this->date_of_birth || !$this->gender) {
            return 0;
        }

        // We need the latest weight, which we don't have yet.
        // For now, let's just pretend a fixed weight. We'll fix this later.
        $weight = 80; // TEMPORARY PLACEHOLDER

        $bmr = (10 * $weight) + (6.25 * $this->height_cm) - (5 * $this->age);

        return $this->gender === 'male' ? $bmr + 5 : $bmr - 161;
    }

    /**
     * Calculates the Total Daily Energy Expenditure (TDEE).
     * BMR plus die Energie, um zur Kaffeemaschine zu laufen.
     */
    public function getTdeeAttribute(): float
    {
        if (!$this->bmr || !$this->activity_level) {
            return 0;
        }

        $multiplier = self::ACTIVITY_MULTIPLIERS[$this->activity_level] ?? 1.2;

        return $this->bmr * $multiplier;
    }

    /**
     * Calculates the target daily calories for weight loss.
     * Aka "Wie viel darf ich essen, um nicht für immer so auszusehen?".
     * Ein Defizit von 500 Kalorien ist ein guter Startpunkt.
     */
    public function getTargetCaloriesAttribute(): int
    {
        return round($this->tdee - 500);
    }

    /**
     * Calculates the Body Mass Index (BMI).
     * Eine Zahl, die dir sagt, wie sehr die Schwerkraft dich mag.
     */
    public function getBmiAttribute(): float
    {
        if (!$this->height_cm) {
            return 0;
        }

        // We need the latest weight for BMI too. Using placeholder.
        $weight = 80; // TEMPORARY PLACEHOLDER
        $heightInMeters = $this->height_cm / 100;

        return round($weight / ($heightInMeters * $heightInMeters), 1);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
