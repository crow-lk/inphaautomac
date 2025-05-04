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

        // Get latest module IDs per serial number where latest module is NOT in CINU pack and created after 07/04/2025
        $excludedSerialNumbers = DB::table('modules as m2')
            ->select('m2.serial_number')
            ->join('battery_packs as bp', 'm2.battery_pack_id', '=', 'bp.id')
            ->where('bp.name', 'LIKE', 'CINU%')
            ->orWhere('bp.name', 'LIKE', 'NU%')
            ->groupBy('m2.serial_number')
            ->havingRaw('COUNT(DISTINCT CASE WHEN bp.name LIKE "CINU%" THEN 1 END) > 0')
            ->havingRaw('COUNT(DISTINCT CASE WHEN bp.name LIKE "NU%" THEN 1 END) > 0');

        $latestValidModuleIds = DB::table('modules as m1')
            ->select(DB::raw('MAX(m1.id) as id'))
            ->join('battery_packs as bp', 'm1.battery_pack_id', '=', 'bp.id')
            ->where('bp.name', 'NOT LIKE', 'CINU%')
            ->where('m1.created_at', '>', $startDate) // Only count modules created after 07/04/2025
            ->whereNotIn('m1.serial_number', $excludedSerialNumbers)
            ->groupBy('m1.serial_number');

        return [
            Stat::make('Total Modules', Module::whereIn('id', $latestValidModuleIds)->count())
                ->url(route('filament.admin.resources.modules.all')),

            Stat::make('Grade A Modules', Module::whereBetween('capacitance', [4000, 6000])
                ->whereIn('id', $latestValidModuleIds)
                ->count())
                ->url(route('filament.admin.resources.modules.grade-a'))
                ->description("Capacity between 4000-6000mAh")
                ->descriptionIcon('heroicon-s-battery-100')
                ->color('success'),

            Stat::make('Grade B Modules', Module::whereBetween('capacitance', [3000, 4000])
                ->whereIn('id', $latestValidModuleIds)
                ->count())
                ->url(route('filament.admin.resources.modules.grade-b'))
                ->description("Capacity between 3000-4000mAh")
                ->descriptionIcon('heroicon-s-battery-50')
                ->color('primary'),

            Stat::make('Grade C Modules', Module::whereBetween('capacitance', [2000, 3000])
                ->whereIn('id', $latestValidModuleIds)
                ->count())
                ->url(route('filament.admin.resources.modules.grade-c'))
                ->description("Capacity between 2000-3000mAh")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Grade D Modules', Module::whereBetween('capacitance', [1000, 2000])
                ->whereIn('id', $latestValidModuleIds)
                ->count())
                ->url(route('filament.admin.resources.modules.grade-d'))
                ->description("Capacity between 1000-2000mAh")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('Grade E Modules', Module::where('capacitance', '<', 1000)
                ->whereIn('id', $latestValidModuleIds)
                ->count())
                ->url(route('filament.admin.resources.modules.grade-e'))
                ->description("Capacity less than 1000mAh")
                ->descriptionIcon('heroicon-s-battery-0')
                ->color('danger'),

            Stat::make('N/A Modules', Module::whereNull('capacitance')
                ->whereIn('id', $latestValidModuleIds)
                ->count())
                ->description("Capacitance not available")
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('gray'),
        ];
    }
}
