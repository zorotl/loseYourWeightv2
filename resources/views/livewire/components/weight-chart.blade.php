<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Listens for the 'weight-saved' event to trigger a re-render.
     */
    #[On('weight-saved')]
    public function refresh(): void
    {
        // Intentionally left blank.
    }

    /**
     * Prepares the weight history data for the chart.
     */
    #[Computed]
    public function chartData(): array
    {
        $history = auth()->user()->weightHistories()->orderBy('weighed_on', 'asc')->get();

        return [
            'labels' => $history->pluck('weighed_on')->map(fn ($date) => $date->format('Y-m-d')),
            'datasets' => [
                [
                    'label' => 'Gewicht (kg)',
                    'data' => $history->pluck('weight_kg'),
                    'borderColor' => 'rgb(79, 70, 229)', // indigo-600
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    'fill' => true,
                    'tension' => 0.2,
                ],
            ],
        ];
    }
}; ?>

<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Gewichtsverlauf</h3>
    
    {{-- This Alpine component now only initializes the chart with the data provided by PHP --}}
    <div
        class="mt-4 h-72"
        wire:key="weight-chart-{{ now() }}" {{-- Add a dynamic key to help Livewire's DOM diffing --}}
        x-data="{
            data: {{ json_encode($this->chartData()) }},
            init() {
                // Ensure Chart.js and its date adapter are registered
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js is not loaded.');
                    return;
                }

                // Destroy any old chart instance to prevent memory leaks on re-render
                if (window.weightChartInstance) {
                    window.weightChartInstance.destroy();
                }

                const ctx = this.$refs.canvas.getContext('2d');
                window.weightChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: this.data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'day',
                                    tooltipFormat: 'dd.MM.yyyy',
                                    displayFormats: {
                                        day: 'dd.MM'
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: false,
                                grid: {
                                    color: '#e5e7eb', // gray-200
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        }"
    >
        <canvas x-ref="canvas"></canvas>
    </div>
</div>