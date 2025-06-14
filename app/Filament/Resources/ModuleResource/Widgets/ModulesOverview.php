<?php

namespace App\Filament\Resources\ModuleResource\Widgets;

use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class ModulesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $startDate = Carbon::create(2025, 4, 7); // Start date: 07/04/2025

        // Step 1: Get latest module IDs per serial number after start date
        $latestModuleIds = DB::table('modules as m1')
            ->select(DB::raw('MAX(m1.id) as id'))
            ->where('m1.created_at', '>', $startDate)
            ->groupBy('m1.serial_number');

        // Step 2: Filter modules to only those where inpha_auto_mac_owned = true
        $latestValidModules = Module::whereIn('id', $latestModuleIds)
            ->where('is_inpha_auto_mac_owned', true);

        return [
            Stat::make('Total Modules', $latestValidModules->count())
                ->url(route('filament.admin.resources.modules.all')),

            Stat::make('Grade A Modules', (clone $latestValidModules)->whereBetween('capacitance', [4000, 6000])->count())
                ->url(route('filament.admin.resources.modules.grade-a'))
                ->description("Capacity between 4000-6000mAh")
                ->descriptionIcon('heroicon-s-battery-100')
                ->color('success'),

            Stat::make('Grade B Modules', (clone $latestValidModules)->whereBetween('capacitance', [3000, 4000])->count())
                ->url(route('filament.admin.resources.modules.grade-b'))
                ->description("Capacity between 3000-4000mAh")
                ->descriptionIcon('heroicon-s-battery-50')
                ->color('primary'),

            Stat::make('Grade C Modules', (clone $latestValidModules)->whereBetween('capacitance', [2000, 3000])->count())
                ->url(route('filament.admin.resources.modules.grade-c'))
                ->description("Capacity between 2000-3000mAh")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Grade D Modules', (clone $latestValidModules)->whereBetween('capacitance', [1000, 2000])->count())
                ->url(route('filament.admin.resources.modules.grade-d'))
                ->description("Capacity between 1000-2000mAh")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('Grade E Modules', (clone $latestValidModules)->where('capacitance', '<', 1000)->count())
                ->url(route('filament.admin.resources.modules.grade-e'))
                ->description("Capacity less than 1000mAh")
                ->descriptionIcon('heroicon-s-battery-0')
                ->color('danger'),

            Stat::make('N/A Modules', (clone $latestValidModules)->whereNull('capacitance')->count())
                ->description("Capacitance not available")
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('gray'),
        ];
    }
}