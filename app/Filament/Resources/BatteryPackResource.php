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
                Forms\Components\Toggle::make('is_brand_new')
                    ->label('Brand New')
                    ->default(false)
                    ->live()
                    ->helperText('Check if this is a brand new battery to use BNU- prefix instead of NU-'),
                Forms\Components\TextInput::make('name')->required()->disabled()->dehydrated(false)->default(function ($record, $get) {
                    // Set a default name only when creating a new record
                    if (is_null($record)) {
                        $nextId = \App\Models\BatteryPack::max('id') + 1;
                        $prefix = $get('is_brand_new') ? 'BNU-' : 'NU-';
                        return $prefix . str_pad($nextId, 7, '0', STR_PAD_LEFT);
                    }
                })
                ->afterStateHydrated(function ($component, $state) {
                    if ($state) {
                        $component->state($state);
                    }
                }),
                Forms\Components\Select::make('no_of_modules')->options([
                    '28' => 'Prius (28 Modules)',
                    '20' => 'Aqua/ Axio (20 Modules)',
                ])
                ->required(),
                Forms\Components\Select::make('vehicle_id')
                    ->relationship('vehicle', 'number')
                    ->nullable()->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('no_of_modules')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('vehicle.number')->label('Vehicle')->sortable()->searchable(),
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

    public static function getWidgets(): array
    {
        return [
            BatteryPackResource\Widgets\BatteryPacksOverview::class,
        ];
    }
}
