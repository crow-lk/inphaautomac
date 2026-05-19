<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemsResource\Pages;
use App\Filament\Resources\ItemsResource\RelationManagers;
use App\Models\Item;
use App\Models\ItemBrand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Inpha Auto Mac Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Select::make('unit')->options([
                'l' => 'liters',
                'ml' => 'mililiters',
                'pcs' => 'pcs',
                'pair' => 'pair',
                ])->required(),
            Forms\Components\TextInput::make('qty')->required(),
            Forms\Components\Select::make('item_brand_id')
                ->label('Brand')
                ->options(ItemBrand::query()->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->nullable()
                ->createOptionUsing(fn (array $data): int => ItemBrand::create($data)->id)
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->unique(ItemBrand::class, 'name')
                        ->maxLength(255),
                ])
                ->createOptionAction(
                    fn (Forms\Components\Actions\Action $action) => $action
                        ->modalHeading('Create Brand')
                        ->successNotificationTitle('Brand created'),
                ),
            Forms\Components\TextInput::make('selling_price')->numeric()->default(0)->prefix('Rs.'),
            Forms\Components\TextInput::make('cost_price')->numeric()->default(0)->prefix('Rs.'),
            Forms\Components\TextInput::make('comment'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('unit')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('qty')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('selling_price')->sortable()->money('LKR'),
                Tables\Columns\TextColumn::make('cost_price')->sortable()->money('LKR'),
                Tables\Columns\TextColumn::make('comment')->sortable()->searchable()])
            ->filters([
                //
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageItems::route('/'),
        ];
    }
}
