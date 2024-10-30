<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatteryPackResource\Pages;
use App\Filament\Resources\BatteryPackResource\RelationManagers;
use App\Models\BatteryPack;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BatteryPackResource extends Resource
{
    protected static ?string $model = BatteryPack::class;

    protected static ?string $navigationIcon = 'heroicon-o-battery-50';

    protected static ?string $navigationGroup = 'Inpha BMS';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //make the name according to the id as NU-000001, NU-000002, etc
                Forms\Components\TextInput::make('name')->required()->disabled()->default(function ($record) {
                    // Set a default name only when creating a new record
                    if (is_null($record)) {
                        $nextId = \App\Models\BatteryPack::max('id') + 1;
                        // $totalCount = \App\Models\BatteryPack::withTrashed()->count() + 1;
                        return 'NU-' . str_pad($nextId, 7, '0', STR_PAD_LEFT);
                    }
                })
                ->afterStateHydrated(function ($component, $state) {
                    // Reflect the saved state in the form
                    if ($state) {
                        $component->state($state);
                    }
                }),
                Forms\Components\Select::make('no_of_modules')->options([
                    //if prius, 28 modules, if Aqua/Axiom, 20 modules
                    '28' => 'Prius (28 Modules)',
                    '20' => 'Aqua/ Axio (20 Modules)',
                ])
                
                ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('no_of_modules')->sortable()->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBatteryPacks::route('/'),
        ];
    }
}
