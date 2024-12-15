<?php

namespace App\Filament\Resources\IssueBatteryPacksResource\Pages;

use App\Filament\Resources\IssueBatteryPacksResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageIssueBatteryPacks extends ManageRecords
{
    protected static string $resource = IssueBatteryPacksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
