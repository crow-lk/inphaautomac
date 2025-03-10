<?php

namespace App\Filament\Resources\ProcurementResource\Pages;

use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\ProcurementResource;
use Filament\Actions;

class ManagePorcurement extends ManageRecords
{
    protected static string $resource = ProcurementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
