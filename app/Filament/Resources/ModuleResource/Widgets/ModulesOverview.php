<?php

namespace App\Filament\Resources\ModuleResource\Widgets;

use App\Models\Module;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ModulesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Modules', Module::whereIn('id', function ($query) {
                $query->selectRaw('MAX(id) AS id')
                    ->from('modules')
                    ->groupBy('serial_number');
            })->count()),
            //group according to the capacity & ir value
            Stat::make('Grade A Modules', Module::whereBetween('capacitance', [4000, 6000])
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id) AS id')
                        ->from('modules')
                        ->whereNotIn('battery_pack_id', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('battery_packs')
                                ->where('name', 'LIKE', 'CINU%'); // Exclude customer-issued battery packs
                        })
                        ->groupBy('serial_number');
                })->count())
                ->description("Capacity between 4000-6000mAh")
                ->descriptionIcon('heroicon-s-battery-100')
                ->color('success'),

            Stat::make('Grade B Modules', Module::whereBetween('capacitance', [3000, 4000])
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id) AS id')
                        ->from('modules')
                        ->whereNotIn('battery_pack_id', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('battery_packs')
                                ->where('name', 'LIKE', 'CINU%');
                        })
                        ->groupBy('serial_number');
                })->count())
                ->description("Capacity between 3000-4000mAh")
                ->descriptionIcon('heroicon-s-battery-50')
                ->color('primary'),

            Stat::make('Grade C Modules', Module::whereBetween('capacitance', [2000, 3000])
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id) AS id')
                        ->from('modules')
                        ->whereNotIn('battery_pack_id', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('battery_packs')
                                ->where('name', 'LIKE', 'CINU%');
                        })
                        ->groupBy('serial_number');
                })->count())
                ->description("Capacity between 2000-3000mAh")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Grade D Modules', Module::whereBetween('capacitance', [1000, 2000])
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id) AS id')
                        ->from('modules')
                        ->whereNotIn('battery_pack_id', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('battery_packs')
                                ->where('name', 'LIKE', 'CINU%');
                        })
                        ->groupBy('serial_number');
                })->count())
                ->description("Capacity between 1000-2000mAh")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('Grade E Modules', Module::where('capacitance', '<', 1000)
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id) AS id')
                        ->from('modules')
                        ->whereNotIn('battery_pack_id', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('battery_packs')
                                ->where('name', 'LIKE', 'CINU%');
                        })
                        ->groupBy('serial_number');
                })->count())
                ->description("Capacity less than 1000mAh")
                ->descriptionIcon('heroicon-s-battery-0')
                ->color('danger'),

            Stat::make('N/A Modules', Module::whereNull('capacitance')
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id) AS id')
                        ->from('modules')
                        ->whereNotIn('battery_pack_id', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('battery_packs')
                                ->where('name', 'LIKE', 'CINU%');
                        })
                        ->groupBy('serial_number');
                })->count())
                ->description("Capacitance not available")
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('gray'),

        ];
    }
}
