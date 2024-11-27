<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ModuleTracker extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Inpha BMS';

    //move this group next to Registrations
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.module-tracker';
}
