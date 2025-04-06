<?php

namespace App\Filament\Resources\BatteryPackResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BatteryPacksOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            
        Stat::make('Total Battery Packs', \App\Models\BatteryPack::whereDate('created_at', '>=', '2025-04-07')->count())->icon('heroicon-o-battery-50'), 
        
        Stat::make('Inpha Auto Mac Owned', \App\Models\BatteryPack::whereDate('created_at', '>=', '2025-04-07')->whereNull('vehicle_id')->count())->icon('heroicon-o-battery-50'), 
        
        Stat::make('Customer Battery Packs', \App\Models\BatteryPack::whereDate('created_at', '>=', '2025-04-07')->whereNotNull('vehicle_id')->count())->icon('heroicon-o-battery-50')];
    }
}
