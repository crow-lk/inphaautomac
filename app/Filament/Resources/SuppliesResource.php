<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuppliesResource\Pages;
use App\Filament\Resources\SuppliesResource\RelationManagers;
use App\Models\Supply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SuppliesResource extends Resource
{
    protected static ?string $model = Supply::class;

    protected static ?string $navigationIcon = 'heroicon-s-beaker';

    protected static ?string $navigationGroup = 'Expenses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_name')->required(),
                Forms\Components\TextInput::make('qty')->numeric()->nullable(),
                Forms\Components\TextInput::make('unit_price')->required()->numeric(),
                Forms\Components\TextInput::make('total')->required()->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('qty')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('unit_price')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('total')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->sortable()->searchable(),
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
            'index' => Pages\ManageSupplies::route('/'),
        ];
    }
}
