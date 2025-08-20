<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
     * Approx. kcal to burn to lose 1kg of body fat.
     */
    private const KCAL_PER_KG_FAT = 7350;

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
        // If the goal is reached or the user wants to gain/maintain, the deficit is 0.
        if (!$this->current_weight_kg || $this->current_weight_kg <= $this->target_weight_kg) {
            return 0;
        }

        // If the target date is in the past, fall back to a sensible default (maintenance).
        if (!$this->target_date || $this->target_date->isPast()) {
            return 0;
        }

        $daysToTarget = now()->diffInDays($this->target_date);

        if ($daysToTarget <= 0) {
            return 0;
        }

        $totalWeightToLose = $this->current_weight_kg - $this->target_weight_kg;
        $totalCaloriesToBurn = $totalWeightToLose * self::KCAL_PER_KG_FAT;

        $dailyDeficit = $totalCaloriesToBurn / $daysToTarget;

        // Cap the deficit to a reasonable maximum.
        return (int) min($dailyDeficit, 1000);
    }

    /**
     * Provides feedback based on the current daily deficit.
     * Ein bisschen Motivation, damit du nicht aufgibst.
     */
    public function getDeficitFeedbackAttribute(): string
    {
        $deficit = $this->daily_deficit;

        return match (true) {
            $deficit <= 0 => 'Im Gleichgewicht. Perfekt, um das Gewicht zu halten.',
            $deficit < 300 => 'Ein sanfter Start. Jede Reise beginnt mit einem kleinen Schritt.',
            $deficit <= 550 => 'Der goldene Mittelweg. Effektiv und nachhaltig. Sehr vernünftig.',
            $deficit <= 750 => 'Ambitioniert! Das bringt schnelle Ergebnisse, aber achte auf deine Energie.',
            $deficit < 1000 => 'Sehr aggressiver Kurs. Pass auf, dass du nicht vom Fleisch fällst!',
            default => 'Maximum erreicht. Eventuell das Zieldatum etwas nach hinten verschieben?',
        };
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
     * Calculates the Body Mass Index (BMI) for the target weight.
     * Eine Vorschau auf die glorreiche, leichtere Zukunft.
     */
    public function getTargetBmiAttribute(): float
    {
        if (!$this->height_cm || !$this->target_weight_kg) {
            return 0;
        }

        $heightInMeters = $this->height_cm / 100;

        return round($this->target_weight_kg / ($heightInMeters * $heightInMeters), 1);
    }

    /**
     * Calculates the user's total calorie goal for the current week.
     */
    public function getWeeklyCalorieGoalAttribute(): int
    {
        return ($this->target_calories ?? 0) * 7;
    }

    /**
     * Calculates the calories consumed so far in the current week (Mon-Sun).
     */
    public function getWeeklyConsumedCaloriesAttribute(): int
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        return $this->foodLogEntries()
            ->whereBetween('consumed_at', [$startOfWeek, $endOfWeek])
            ->sum('calories');
    }

    /**
     * A user has many weight history records.
     */
    public function weightHistories()
    {
        return $this->hasMany(WeightHistory::class)->orderBy('weighed_on', 'desc');
    }

    /**
     * A user has many food log entries.
     * Diese Methode stellt die Beziehung zu den FoodLogEntry-Modellen her.
     */
    public function foodLogEntries()
    {
        return $this->hasMany(FoodLogEntry::class);
    }

    /**
     * The food items manually created by this user.
     */
    public function createdFoods()
    {
        return $this->hasMany(Food::class, 'creator_id');
    }

    /**
     * A user has many meals.
     * Diese Methode stellt die Beziehung zu den Meal-Modellen her.
     */
    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    /**
     * Lädt die verbleibende Zeit bis zum Zieldatum.
     * Gibt null zurück, wenn das Zieldatum in der Vergangenheit liegt oder nicht gesetzt ist.
     */
    public function getGoalTimeRemainingAttribute(): ?string
    {
        if (!$this->target_date || $this->target_date->isPast()) {
            return null;
        }

        return 'in ' . now()->diffForHumans($this->target_date, CarbonInterface::DIFF_ABSOLUTE);
    }

    public function favoriteFoods()
    {
        return $this->belongsToMany(Food::class, 'food_user_favorites');
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * A user can have many feedback entries.
     */
    public function feedback()
    {
        return $this->hasMany(\App\Models\Feedback::class);
    }
}
