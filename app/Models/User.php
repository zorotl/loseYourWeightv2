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
        'target_date',
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
            'target_date' => 'date',
        ];
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
     * Gets the user's most recent weight from their history.
     * Oder null, wenn der Benutzer zu faul war, etwas einzutragen.
     */
    public function getCurrentWeightKgAttribute(): ?float
    {
        return $this->weightHistories()->first()?->weight_kg;
    }

    /**
     * Calculates the Basal Metabolic Rate (BMR) using Mifflin-St Jeor.
     * Das ist die Energie, die man verbraucht, wenn man nur rumliegt und auf den Tod wartet.
     */
    public function getBmrAttribute(): float
    {
        // If we don't have the necessary data, we can't calculate anything. Simple as that.
        if (!$this->height_cm || !$this->date_of_birth || !$this->gender || !$this->current_weight_kg) {
            return 0;
        }

        $bmr = (10 * $this->current_weight_kg) + (6.25 * $this->height_cm) - (5 * $this->age);

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
     * Calculates the required daily deficit based on the target date.
     * Oder gibt einen Standardwert zurück, wenn der Benutzer zu faul war, ein Datum einzugeben.
     */
    public function getDailyDeficitAttribute(): int
    {
        if (!$this->target_date || !$this->current_weight_kg || $this->current_weight_kg <= $this->target_weight_kg) {
            return 500; // Standard-Defizit
        }

        $daysToTarget = now()->diffInDays($this->target_date);

        if ($daysToTarget <= 0) {
            return 500; // Zieldatum ist in der Vergangenheit, nimm Standard
        }

        $totalWeightToLose = $this->current_weight_kg - $this->target_weight_kg;
        $totalCaloriesToBurn = $totalWeightToLose * 7700; // ca. 7700 kcal pro kg Fett

        // Wir deckeln das Defizit, um absurden Hungerkuren vorzubeugen.
        $dailyDeficit = $totalCaloriesToBurn / $daysToTarget;

        return (int) min($dailyDeficit, 1000); // Maximal 1000 kcal Defizit pro Tag
    }

    /**
     * Calculates the target daily calories for weight loss.
     * Nutzt jetzt unser dynamisches Defizit.
     */
    public function getTargetCaloriesAttribute(): int
    {
        return round($this->tdee - $this->daily_deficit);
    }

    /**
     * Calculates the Body Mass Index (BMI).
     * Eine Zahl, die dir sagt, wie sehr die Schwerkraft dich mag.
     */
    public function getBmiAttribute(): float
    {
        if (!$this->height_cm || !$this->current_weight_kg) {
            return 0;
        }

        $heightInMeters = $this->height_cm / 100;

        return round($this->current_weight_kg / ($heightInMeters * $heightInMeters), 1);
    }

    /**
     * A user has many weight history records.
     */
    public function weightHistories()
    {
        return $this->hasMany(WeightHistory::class)->orderBy('weighed_on', 'desc');
    }

}
