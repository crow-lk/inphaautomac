<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Filament\Resources\ModuleResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Maatwebsite\Excel\Facades\Excel;

class ManageModules extends ManageRecords
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('Import Modules')
                ->modal('importModules', [
                    'title' => 'Import Modules',
                    'width' => '2xl',
                ])->form(function () {
                    return [
                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label('CSV File'),
                        \Filament\Forms\Components\Select::make('module_type')
                            ->label('Module Type')
                            ->options([
                                'NU' => 'NU',
                                'CINU' => 'CINU',
                            ])
                            ->required(),
                    ];
                })->action(function (array $data) {
                    $file = public_path('storage/' . $data['file']);
                    Excel::import(new \App\Imports\ModulesImport($data['module_type']), $file);
                }),
        ];
    }
}
