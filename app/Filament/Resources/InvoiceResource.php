<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Service;
use App\Models\Vehicle;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Invoicing';

    protected static ?string $modelLabel = 'Job';

    protected static ?string $pluralModelLabel = 'Jobs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Job details')->schema([
                self::exclusiveToggle('is_invoice', 'Job Invoice', 'is_quatation'),
                self::exclusiveToggle('is_quatation', 'Job Quotation', 'is_invoice'),
                Select::make('customer_id')->relationship('customer', 'name')->searchable()->required()->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('vehicle_id', null);
                        $set('model', null);
                    }),
                Select::make('vehicle_id')->label('Vehicle')
                    ->relationship('vehicle', 'number', fn (Builder $query, Get $get) => $query->where('customer_id', $get('customer_id')))->preload()
                    ->searchable()->required()->live()
                    ->disabled(fn (Get $get): bool => ! $get('customer_id'))
                    ->afterStateUpdated(fn ($state, Set $set) => $set('model', Vehicle::find($state)?->model)),
                TextInput::make('model')->required()->maxLength(255),
                TextInput::make('mileage')->numeric()->minValue(0)->required(),
                self::exclusiveToggle('is_km', 'Kilometres', 'is_miles')->default(true),
                self::exclusiveToggle('is_miles', 'Miles', 'is_km')->default(false),
            ])->columns(2),
            Section::make('Job items')->description('Search or create a part or service, enter its details, then confirm to add it.')
                ->schema([self::catalogueSelect('item'), self::catalogueSelect('service'), self::itemsRepeater()])->columns(2),
            Section::make('Totals')->schema([
                TextInput::make('amount')->label('Total amount')->prefix('LKR')->numeric()->default(0)->readOnly(),
                TextInput::make('credit_balance')->label('To pay')->prefix('LKR')->numeric()->default(0)->readOnly()
                    ->helperText('Payments and applicable discounts are deducted automatically.'),
            ])->columns(2),
        ])->columns(1);
    }

    private static function exclusiveToggle(string $name, string $label, string $other): Toggle
    {
        return Toggle::make($name)->label($label)->live()
            ->afterStateUpdated(function (bool $state, Set $set) use ($other) {
                if ($state) {
                    $set($other, false);
                }
            });
    }

    private static function itemsRepeater(): Repeater
    {
        return Repeater::make('items')->relationship('invoiceItems')->hiddenLabel()
            ->addable(false)->defaultItems(0)->minItems(1)->columnSpanFull()
            ->view('filament.forms.components.job-items-table')->reorderable(false)->columns(2)
            ->extraItemActions([
                Action::make('confirmPrice')->label('Confirm changes')->icon('heroicon-m-check')->color('warning')
                    ->extraAttributes(['style' => 'background-color: #eab308; color: #ffffff;'])
                    ->tooltip('Confirm price, quantity and discount and update totals')
                    ->action(function (array $arguments, Repeater $component): void {
                        $row = $component->getItemState($arguments['item']);
                        $items = $component->getState();
                        $items[$arguments['item']]['confirmed_values'] = ['price' => $row['price'], 'quantity' => $row['quantity'], 'discount' => $row['discount'] ?? 0];
                        $component->state($items);
                        $component->callAfterStateUpdated();
                    }),
            ])
            ->itemLabel(function (array $state): string {
                $service = (bool) ($state['is_service'] ?? false);
                $name = $service
                    ? Service::find($state['service_id'] ?? null)?->name
                    : Item::find($state['item_id'] ?? null)?->name;

                return ($name ?? ($service ? 'Service' : 'Item'))
                    .' | Qty '.($state['quantity'] ?? 1).' | LKR '.number_format(self::itemsTotal([$state]), 2);
            })
            ->schema([
                Hidden::make('confirmed_values')->dehydrated(false)
                    ->afterStateHydrated(function (Hidden $component, Get $get): void {
                        $component->state(['price' => $get('price'), 'quantity' => $get('quantity'), 'discount' => $get('discount') ?? 0]);
                    }),
                Toggle::make('is_service')->label('Service')->live()
                    ->afterStateUpdated(function (bool $state, Set $set) {
                        $set('is_item', ! $state);
                        if ($state) {
                            $set('item_id', null);
                            $set('quantity', 1);
                        } else {
                            $set('service_id', null);
                        }
                    }),
                Toggle::make('is_item')->label('Item')->default(true)->live()
                    ->afterStateUpdated(function (bool $state, Set $set) {
                        $set('is_service', ! $state);
                        if ($state) {
                            $set('service_id', null);
                        } else {
                            $set('item_id', null);
                            $set('quantity', 1);
                        }
                    }),
                Select::make('service_id')->label('Service')->relationship('service', 'name')->searchable()->preload()->dehydratedWhenHidden()
                    ->visible(fn (Get $get): bool => (bool) $get('is_service'))
                    ->required(fn (Get $get): bool => (bool) $get('is_service'))
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(255),
                        Textarea::make('description'),
                    ])->createOptionUsing(fn (array $data) => Service::create($data)->getKey())->columnSpanFull(),
                Select::make('item_id')->label('Item')->relationship('item', 'name')->searchable()->preload()->live()->dehydratedWhenHidden()
                    ->visible(fn (Get $get): bool => (bool) $get('is_item'))
                    ->required(fn (Get $get): bool => (bool) $get('is_item'))
                    ->afterStateUpdated(function ($state, Set $set, Select $component) {
                        $set('price', Item::find($state)?->selling_price ?? 0);
                        $component->getParentRepeater()?->callAfterStateUpdated();
                    })
                    ->createOptionForm(self::newItemSchema())
                    ->createOptionUsing(fn (array $data) => Item::create($data)->getKey())->columnSpanFull(),
                TextInput::make('quantity')->numeric()->required()->default(1)->minValue(0.001)->live(condition: false)
                    ->disabled(fn (Get $get): bool => (bool) $get('is_service'))->dehydrated(),
                TextInput::make('price')->label('Unit price')->prefix('LKR')->numeric()->required()->minValue(0)->live(condition: false),
                TextInput::make('discount')->label('Line discount')->prefix('LKR')->numeric()->nullable()->default(0)->dehydrateStateUsing(fn ($state) => $state ?? 0)->minValue(0)
                    ->maxValue(fn (Get $get): float => (float) ($get('quantity') ?? 1) * (float) ($get('price') ?? 0))->live(condition: false),
                Toggle::make('warranty_available')->label('Warranty available')->live()
                    ->afterStateUpdated(function (bool $state, Set $set) {
                        if (! $state) {
                            $set('warranty_type', null);
                        }
                    }),
                Select::make('warranty_type')->options([
                    '1 month' => '1 Month', '3 months' => '3 Months', '6 months' => '6 Months',
                    '1 year' => '1 Year', '2 years' => '2 Years', '3 years' => '3 Years',
                ])->required(fn (Get $get): bool => (bool) $get('warranty_available'))
                    ->visible(fn (Get $get): bool => (bool) $get('warranty_available'))->dehydratedWhenHidden()->dehydrated(),
            ])
            // The repeater receives the parent Invoice; its child fields receive InvoiceItem.
            ->afterStateUpdated(function (?array $state, Set $set, ?Invoice $record) {
                $total = self::itemsTotal($state ?? []);
                $set('amount', $total);
                $set('credit_balance', self::outstandingBalance($total, $record));
            });
    }

    public static function validateConfirmedItems(array $items, string $statePath = 'data'): void
    {
        $errors = [];
        foreach ($items as $key => $item) {
            foreach (['price', 'quantity', 'discount'] as $field) {
                if (! array_key_exists($field, $item['confirmed_values'] ?? [])
                    || (string) ($item[$field] ?? ($field === 'discount' ? 0 : '')) !== (string) $item['confirmed_values'][$field]) {
                    $errors[$statePath.'.items.'.$key.'.'.$field] = 'Click the yellow confirm button (✓) to confirm this row before saving the invoice.';
                }
            }
        }
        if ($errors !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }
    private static function itemDetailsAction(string $type): Action
    {
        $model = $type === 'service' ? Service::class : Item::class;

        return Action::make('addDetails')->label('Add')->icon('heroicon-m-plus')->button()
            ->tooltip('Enter details and add to job')
            ->disabled(fn (Select $component): bool => blank($component->getState()))
            ->modalHeading(fn (Select $component): string => 'Add '.($model::find($component->getState())?->name ?? $type))
            ->modalWidth('2xl')->modalSubmitActionLabel('Confirm and add')->closeModalByClickingAway(false)
            ->fillForm(fn (Select $component): array => [
                'catalogue_id' => $component->getState(),
                'quantity' => 1,
                'price' => $type === 'item' ? (Item::find($component->getState())?->selling_price ?? 0) : 0,
                'warranty_available' => false,
                'warranty_type' => null,
            ])
            ->form([
                Hidden::make('catalogue_id')->required()->exists($model, 'id'),
                TextInput::make('quantity')->numeric()->required()->default(1)->minValue(0.001)
                    ->disabled($type === 'service')->dehydrated(),
                TextInput::make('price')->label('Unit price')->prefix('LKR')->numeric()->required()->default(0)->minValue(0),
                TextInput::make('discount')->label('Line discount')->prefix('LKR')->numeric()->nullable()->default(0)->dehydrateStateUsing(fn ($state) => $state ?? 0)->minValue(0)
                    ->maxValue(fn (Get $get): float => (float) ($get('quantity') ?? 1) * (float) ($get('price') ?? 0))->live(onBlur: true),
                Toggle::make('warranty_available')->label('Warranty available')->default(false)->live()
                    ->afterStateUpdated(function (bool $state, Set $set) {
                        if (! $state) {
                            $set('warranty_type', null);
                        }
                    }),
                Select::make('warranty_type')->options([
                    '1 month' => '1 Month', '3 months' => '3 Months', '6 months' => '6 Months',
                    '1 year' => '1 Year', '2 years' => '2 Years', '3 years' => '3 Years',
                ])->required(fn (Get $get): bool => (bool) $get('warranty_available'))
                    ->visible(fn (Get $get): bool => (bool) $get('warranty_available'))->dehydratedWhenHidden()->dehydrated(),
            ])
            ->action(function (array $data, Select $component) use ($type): void {
                $service = $type === 'service';
                $entry = [
                    'is_service' => $service,
                    'is_item' => ! $service,
                    'service_id' => $service ? $data['catalogue_id'] : null,
                    'item_id' => $service ? null : $data['catalogue_id'],
                    'quantity' => $service ? 1 : $data['quantity'],
                    'price' => $data['price'],
                    'discount' => $data['discount'] ?? 0,
                    'confirmed_values' => ['price' => $data['price'], 'quantity' => $service ? 1 : $data['quantity'], 'discount' => $data['discount'] ?? 0],
                    'warranty_available' => (bool) ($data['warranty_available'] ?? false),
                    'warranty_type' => ($data['warranty_available'] ?? false) ? ($data['warranty_type'] ?? null) : null,
                ];
                $repeater = collect($component->getContainer()->getComponents())
                    ->first(fn ($field): bool => $field instanceof Repeater);
                $items = $repeater->getState() ?? [];
                $items[(string) \Illuminate\Support\Str::uuid()] = $entry;
                $repeater->state($items);
                $repeater->callAfterStateUpdated();
                $component->state(null);
            });
    }

    private static function catalogueSelect(string $type): Select
    {
        $model = $type === 'service' ? Service::class : Item::class;

        return Select::make('pick_'.$type.'_id')->label($type === 'item' ? 'Add items' : 'Add services')
            ->placeholder('Search or create '.($type === 'item' ? 'an item' : 'a service'))
            ->searchable()->preload()->live()->dehydrated(false)
            ->options(fn (): array => $model::query()->orderBy('name')->limit(50)->pluck('name', 'id')->all())
            ->getSearchResultsUsing(fn (string $search): array => $model::query()
                ->where('name', 'like', '%'.$search.'%')->orderBy('name')->limit(50)->pluck('name', 'id')->all())
            ->getOptionLabelUsing(fn ($value): ?string => $model::find($value)?->name)
            ->createOptionForm($type === 'service' ? [
                TextInput::make('name')->required()->maxLength(255),
                Textarea::make('description'),
            ] : self::newItemSchema())
            ->createOptionUsing(fn (array $data) => $model::create($data)->getKey())
            ->suffixAction(self::itemDetailsAction($type))
            ->helperText('Select or create, then click Add to enter details.');
    }

    private static function newItemSchema(): array
    {
        return [
            TextInput::make('name')->label('Item name')->required()->maxLength(255),
            Select::make('unit')->options(['l' => 'Liters', 'ml' => 'Milliliters', 'pcs' => 'Pieces', 'pair' => 'Pair'])->required(),
            TextInput::make('qty')->label('Quantity')->numeric()->required()->minValue(0),
            TextInput::make('selling_price')->numeric()->default(0)->minValue(0),
            TextInput::make('cost_price')->numeric()->default(0)->minValue(0),
            Textarea::make('comment'),
        ];
    }

    private static function itemsTotal(array $items): float
    {
        return (float) collect($items)->sum(fn (array $item): float => max(0, (($item['is_service'] ?? false) ? 1 : (float) ($item['quantity'] ?? 0)) * (float) ($item['price'] ?? 0) - (float) ($item['discount'] ?? 0)));
    }

    private static function outstandingBalance(float $total, ?Invoice $invoice): float
    {
        $paid = $invoice?->payments()->get()->sum(fn ($payment): float => (float) $payment->amount_paid + ($payment->discount_available ? (float) $payment->discount : 0)) ?? 0;

        return max(0, $total - $paid);
    }

    public static function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn (Builder $query) => $query->with(['customer', 'vehicle.brand']))
            ->defaultSort('created_at', 'desc')->striped()->paginated([10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Job ID')->sortable()->searchable()
                    ->formatStateUsing(fn ($state, Invoice $record): string => $state.' - '.($record->is_invoice ? 'Job' : 'Quotation')),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->sortable()->searchable()
                    ->formatStateUsing(fn ($state, Invoice $record): string => trim(($record->customer?->title ?? '').' '.$state)),
                Tables\Columns\TextColumn::make('vehicle.number')->label('Vehicle No.')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('model')->sortable()->searchable()->toggleable()
                    ->formatStateUsing(fn ($state, Invoice $record): string => trim(
                        ($record->vehicle?->getRelation('brand')?->name ?? $record->vehicle?->getRawOriginal('brand') ?? '').' '.$state)),
                Tables\Columns\TextColumn::make('mileage')->sortable()->toggleable()
                    ->formatStateUsing(fn ($state, Invoice $record): string => $state.($record->is_km ? ' km' : ' miles')),
                Tables\Columns\TextColumn::make('amount')->label('Total amount')->money('LKR')->sortable(),
                Tables\Columns\TextColumn::make('credit_balance')->label('To pay')->money('LKR')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('payment_status')->label('Status')->badge()->sortable()
                    ->color(fn (?string $state): string => match ($state) {
                        'Partial Paid' => 'warning', 'Paid' => 'success', 'Unpaid' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Created')->dateTime()->sortable()->toggleable(),
            ])->filters([
                Tables\Filters\SelectFilter::make('payment_status')->label('Payment status')->options([
                    'Unpaid' => 'Unpaid', 'Partial Paid' => 'Partial Paid', 'Paid' => 'Paid',
                ]),
                Tables\Filters\TernaryFilter::make('is_invoice')->label('Job type')->trueLabel('Invoices')->falseLabel('Quotations'),
            ])->actions([
                Tables\Actions\EditAction::make()->url(fn (Invoice $record): string => self::getUrl('edit', ['record' => $record])),
                Tables\Actions\Action::make('download_pdf')->label('PDF')->icon('heroicon-o-printer')
                    ->url(fn (Invoice $record): string => route('invoices.pdf', $record->id))->openUrlInNewTab(),
            ])->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
