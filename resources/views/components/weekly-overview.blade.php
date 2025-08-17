@php
    $user = auth()->user();
    $weeklyGoal = $user->weekly_calorie_goal;
    $weeklyConsumed = $user->weekly_consumed_calories;
    $difference = $weeklyGoal - $weeklyConsumed;
    $percentage = $weeklyGoal > 0 ? min(100, round(($weeklyConsumed / $weeklyGoal) * 100)) : 0;
@endphp

<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
    <div class="flex flex-col gap-2">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Aktuelle Wochenübersicht ({{ now()->startOfWeek()->format('d.m') }} - {{ now()->endOfWeek()->format('d.m.Y') }})
        </h3>
        <p class="text-sm text-gray-500">
            Verbraucht: <span class="font-bold">{{ $weeklyConsumed }}</span> / {{ $weeklyGoal }} kcal
        </p>
        <div class="w-full pt-1">
            <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-2.5 rounded-full {{ $percentage > 100 ? 'bg-red-500' : 'bg-indigo-600' }}" style="width: {{ min(100, $percentage) }}%"></div>
            </div>
        </div>
        <p class="text-right text-xs {{ $difference < 0 ? 'text-red-500' : 'text-green-500' }}">
            @if($difference < 0)
                {{ abs($difference) }} kcal über dem Ziel
            @else
                {{ $difference }} kcal übrig
            @endif
        </p>
    </div>
</div>