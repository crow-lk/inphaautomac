<?php

namespace App\Filament\Resources\SuppliesResource\Pages;

use App\Filament\Resources\SuppliesResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSupplies extends ManageRecords
{
    protected static string $resource = SuppliesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
