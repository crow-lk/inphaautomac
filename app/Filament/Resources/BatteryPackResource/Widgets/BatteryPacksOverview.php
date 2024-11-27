<?php

namespace App\Filament\Resources\BatteryPackResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BatteryPacksOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            //count total battery packs
            Stat::make('Total Battery Packs', \App\Models\BatteryPack::count())->icon('heroicon-o-battery-50'),

            //count total battery packs where vehicle_id is null (inpha auto mac owned)
            Stat::make('Inpha Auto Mac Owned', \App\Models\BatteryPack::whereNull('vehicle_id')->count())->icon('heroicon-o-battery-50'),

            //count total battery packs where vehicle_id is not null (customer owned)
            Stat::make('Customer Battery Packs', \App\Models\BatteryPack::whereNotNull('vehicle_id')->count())->icon('heroicon-o-battery-50'),
        ];
    }
}
