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
        ];
    }
}
