<?php

namespace App\Filament\Resources\BatteryPackResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BatteryPacksOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Calculate all battery packs
        $allBatteryPacks = \App\Models\BatteryPack::whereDate('created_at', '>=', '2025-04-07')->count();

        // Calculate customer battery packs
        $customerBatteryPacks = \App\Models\BatteryPack::whereDate('created_at', '>=', '2025-04-07')->whereNotNull('vehicle_id')->count();

        // Calculate inphaAutoMacOwned battery packs
        $inphaAutoMacOwned = \App\Models\BatteryPack::whereDate('created_at', '>=', '2025-04-07')->whereNull('vehicle_id')->count();

        // Calculate the count of battery packs not owned by customers
        $totalBatteryPacks = $allBatteryPacks - $customerBatteryPacks;
        $inphaAutoMac = $inphaAutoMacOwned - $customerBatteryPacks;

        return [
            Stat::make('Total Battery Packs', $totalBatteryPacks)->icon('heroicon-o-battery-50'),
            Stat::make('Inpha Auto Mac Owned', $inphaAutoMac)->icon('heroicon-o-battery-50'),
            Stat::make('Customer Battery Packs', $customerBatteryPacks)->icon('heroicon-o-battery-50'),
        ];
    }
}
