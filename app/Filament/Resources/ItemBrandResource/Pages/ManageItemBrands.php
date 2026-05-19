<?php

namespace App\Filament\Resources\ItemBrandResource\Pages;

use App\Filament\Resources\ItemBrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageItemBrands extends ManageRecords
{
    protected static string $resource = ItemBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
