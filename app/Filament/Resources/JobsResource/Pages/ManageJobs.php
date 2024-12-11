<?php

namespace App\Filament\Resources\JobsResource\Pages;

use App\Filament\Resources\JobsResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJobs extends ManageRecords
{
    protected static string $resource = JobsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
