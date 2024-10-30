<?php

namespace App\Filament\Resources\BatteryPackResource\Pages;

use App\Filament\Resources\BatteryPackResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBatteryPacks extends ManageRecords
{
    protected static string $resource = BatteryPackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
