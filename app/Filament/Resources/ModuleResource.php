<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleResource\Pages;
use App\Filament\Resources\ModuleResource\RelationManagers;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Inpha Battery Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('serial_number')->required(),
                Forms\Components\TextInput::make('ir_value')->required(),
                Forms\Components\TextInput::make('capacitance')->required(),
                Forms\Components\Select::make('battery_pack_id')->relationship('batteryPack', 'name')->required()->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('ir_value')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('capacitance')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('batteryPack.name') // Add this line
            ->label('Battery Pack')
            ->sortable()
            ->searchable(),
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
            'index' => Pages\ManageModules::route('/'),
        ];
    }
}
