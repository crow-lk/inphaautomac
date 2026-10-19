<?php

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Component;

class InvoiceResourceTestCase extends Tests\TestCase
{
    public function createApplication()
    {
        // Test the resource independently of unrelated panel discovery errors.
        $app = Illuminate\Foundation\Application::configure(basePath: dirname(__DIR__, 2))
            ->withProviders([], withBootstrapProviders: false)
            ->withMiddleware()
            ->withExceptions()
            ->create();
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}

uses(InvoiceResourceTestCase::class);
class InvoiceItemsTablePreview extends Component implements HasForms
{
    use InteractsWithForms;

    public array $data = [];

    public function mount(array $rows): void
    {
        $this->data = ['items' => $rows];
    }

    public function form(Form $form): Form
    {
        $invoice = new Invoice;
        $invoice->setRelation('invoiceItems', new Collection);
        $form = InvoiceResource::form($form->model($invoice)->statePath('data'));
        $repeater = collect($form->getFlatComponents(true))->first(fn ($field) => $field instanceof Filament\Forms\Components\Repeater);

        return $form->schema([$repeater]);
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}
function invoiceResourceForm(?Invoice $invoice = null): array
{
    $livewire = new class extends Component implements HasForms
    {
        use InteractsWithForms;

        public array $data = [];

        public array $popup = [];
    };

    $form = InvoiceResource::form(Form::make($livewire)->statePath('data')->model($invoice ?? Invoice::class));

    return [$livewire, $form];
}

it('updates a long existing invoice when an existing item price changes', function () {
    $invoice = Mockery::mock(Invoice::class)->makePartial();
    $invoice->setRelation('invoiceItems', new Collection([
        new InvoiceItem(['id' => 1, 'quantity' => 2, 'price' => 50, 'is_item' => true]),
    ]));
    // Fillable protection excludes id, so assign the persisted item key explicitly.
    $invoice->invoiceItems->first()->id = 1;
    $payments = Mockery::mock(HasMany::class);
    $payments->shouldReceive('get')->andReturn(new Collection([
        new Payment(['amount_paid' => 100, 'discount_available' => true, 'discount' => 20]),
    ]));
    $invoice->shouldReceive('payments')->andReturn($payments);
    [$livewire, $form] = invoiceResourceForm($invoice);
    $livewire->data = ['items' => ['record-1' => ['quantity' => 2, 'price' => 75, 'is_item' => true]]];
    for ($i = 2; $i <= 100; $i++) {
        $livewire->data['items']['new-'.$i] = ['quantity' => 1, 'price' => 10, 'is_item' => true];
    }

    $form->callAfterStateUpdated('data.items.record-1.price');

    expect($livewire->data['amount'])->toBe(1140.0)
        ->and($livewire->data['credit_balance'])->toBe(1020.0);
});

it('recalculates new invoice quantities and row deletion without a record', function () {
    [$livewire, $form] = invoiceResourceForm();
    $livewire->data = ['items' => [
        'part' => ['quantity' => 3, 'price' => 25],
        'service' => ['is_service' => true, 'quantity' => 9, 'price' => 100],
    ]];
    // The repeater owns the calculation, including service quantity normalization.
    $form->callAfterStateUpdated('data.items');
    expect($livewire->data['amount'])->toBe(175.0)
        ->and($livewire->data['credit_balance'])->toBe(175.0);

    unset($livewire->data['items']['part']);
    $form->callAfterStateUpdated('data.items');
    expect($livewire->data['amount'])->toBe(100.0);

    $livewire->data['items'] = [];
    $form->callAfterStateUpdated('data.items');
    expect($livewire->data['amount'])->toBe(0.0)
        ->and($livewire->data['credit_balance'])->toBe(0.0);
});

it('refreshes totals after selecting a different item and switching to a service', function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    Illuminate\Support\Facades\Schema::create('items', function ($table) {
        $table->id();
        $table->string('name');
        $table->decimal('selling_price', 12, 2);
        $table->softDeletes();
    });
    Illuminate\Support\Facades\DB::table('items')->insert(['id' => 5, 'name' => 'Part', 'selling_price' => 250]);
    $invoice = Mockery::mock(Invoice::class)->makePartial();
    $invoice->setRelation('invoiceItems', new Collection);
    $payments = Mockery::mock(HasMany::class);
    $payments->shouldReceive('get')->andReturn(new Collection);
    $invoice->shouldReceive('payments')->andReturn($payments);
    [$livewire, $form] = invoiceResourceForm($invoice);
    $livewire->data = ['items' => ['new' => ['item_id' => 5, 'quantity' => 3, 'price' => 10, 'is_item' => true, 'is_service' => false]]];

    $form->callAfterStateUpdated('data.items.new.item_id');
    expect((float) $livewire->data['items']['new']['price'])->toBe(250.0)
        ->and($livewire->data['amount'])->toBe(750.0)
        ->and($livewire->data['credit_balance'])->toBe(750.0);

    $livewire->data['items']['new']['is_service'] = true;
    $form->callAfterStateUpdated('data.items.new.is_service');
    expect($livewire->data['items']['new']['quantity'])->toBe(1)
        ->and($livewire->data['items']['new']['item_id'])->toBeNull()
        ->and($livewire->data['amount'])->toBe(250.0);
});

it('keeps popup drafts outside the item list and appends only on confirmation', function () {
    [$livewire, $form] = invoiceResourceForm();
    $livewire->data = ['items' => ['existing' => ['quantity' => 2, 'price' => 30, 'is_item' => true]]];
    $repeater = collect($form->getFlatComponents(true))->first(fn ($component) => $component instanceof Filament\Forms\Components\Repeater);
    $picker = collect($form->getFlatComponents(true))->first(fn ($field) => $field instanceof Filament\Forms\Components\Select && $field->getName() === 'pick_item_id');
    $picker->state(5);
    $action = $picker->getAction('addDetails');

    expect($action->isWizard())->toBeFalse()
        ->and($livewire->data['items'])->toHaveCount(1);

    $action->call(['data' => [
        'catalogue_id' => 5, 'quantity' => 3, 'price' => 100,
        'warranty_available' => true, 'warranty_type' => '1 year',
    ]]);
    $items = $livewire->data['items'];
    $added = end($items);
    expect($items)->toHaveCount(2)
        ->and($items['existing']['price'])->toBe(30)
        ->and($added['item_id'])->toBe(5)
        ->and($added['service_id'])->toBeNull()
        ->and($added['warranty_type'])->toBe('1 year')
        ->and($livewire->data['amount'])->toBe(360.0)
        ->and($livewire->data['credit_balance'])->toBe(360.0);
});

it('normalizes confirmed service quantities and clears inactive warranty details', function () {
    [$livewire, $form] = invoiceResourceForm();
    $livewire->data = ['items' => []];
    $repeater = collect($form->getFlatComponents(true))->first(fn ($component) => $component instanceof Filament\Forms\Components\Repeater);
    $picker = collect($form->getFlatComponents(true))->first(fn ($field) => $field instanceof Filament\Forms\Components\Select && $field->getName() === 'pick_service_id');
    $picker->state(7);
    $picker->getAction('addDetails')->call(['data' => [
        'catalogue_id' => 7,
        'quantity' => 9, 'price' => 250, 'warranty_available' => false, 'warranty_type' => '1 year',
    ]]);
    $entry = array_values($livewire->data['items'])[0];

    expect($entry['is_service'])->toBeTrue()
        ->and($entry['is_item'])->toBeFalse()
        ->and($entry['item_id'])->toBeNull()
        ->and($entry['quantity'])->toBe(1)
        ->and($entry['warranty_type'])->toBeNull()
        ->and($livewire->data['amount'])->toBe(250.0);
});

it('validates popup details without changing the invoice draft', function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    Illuminate\Support\Facades\Schema::create('items', function ($table) {
        $table->id();
        $table->string('name');
        $table->decimal('selling_price', 12, 2);
        $table->softDeletes();
    });
    Illuminate\Support\Facades\DB::table('items')->insert(['id' => 1, 'name' => 'Test part', 'selling_price' => 150]);
    $invoice = Mockery::mock(Invoice::class)->makePartial();
    $invoice->setRelation('invoiceItems', new Collection);
    $payments = Mockery::mock(HasMany::class);
    $payments->shouldReceive('get')->andReturn(new Collection);
    $invoice->shouldReceive('payments')->andReturn($payments);
    [$livewire, $form] = invoiceResourceForm($invoice);
    $livewire->data = ['items' => []];
    $repeater = collect($form->getFlatComponents(true))->first(fn ($component) => $component instanceof Filament\Forms\Components\Repeater);
    $picker = collect($form->getFlatComponents(true))->first(fn ($field) => $field instanceof Filament\Forms\Components\Select && $field->getName() === 'pick_item_id');
    $picker->state(5);
    $action = $picker->getAction('addDetails');
    $modal = $action->getForm(Form::make($livewire)->statePath('popup')->model(Invoice::class));
    $picker->state(1);
    $action->mount(['form' => $modal]);
    $livewire->popup['item_id'] = 1;
    $livewire->popup['quantity'] = 2;
    $livewire->popup['price'] = 150;
    $livewire->popup['warranty_available'] = true;

    expect(fn () => $modal->getState())->toThrow(Illuminate\Validation\ValidationException::class);
    $livewire->popup['warranty_type'] = '1 year';
    $validated = $modal->getState();
    expect($livewire->data['items'])->toBeEmpty()
        ->and($validated['catalogue_id'])->toBe(1)
        ->and($validated['quantity'])->toBe(2);

    $action->call(['data' => $validated]);
    expect($livewire->data['items'])->toHaveCount(1)
        ->and($livewire->data['amount'])->toBe(300.0);
});

it('adds multiple items and services from the inline pickers without replacing earlier rows', function () {
    [$livewire, $form] = invoiceResourceForm();
    $livewire->data = ['items' => []];
    $fields = collect($form->getFlatComponents(true));
    $itemPicker = $fields->first(fn ($field) => $field instanceof Filament\Forms\Components\Select && $field->getName() === 'pick_item_id');
    $servicePicker = $fields->first(fn ($field) => $field instanceof Filament\Forms\Components\Select && $field->getName() === 'pick_service_id');

    $itemPicker->state(5);
    $itemPicker->getAction('addDetails')->call(['data' => ['catalogue_id' => 5, 'quantity' => 2, 'price' => 100]]);
    expect($itemPicker->getState())->toBeNull();
    $servicePicker->state(7);
    $servicePicker->getAction('addDetails')->call(['data' => ['catalogue_id' => 7, 'quantity' => 1, 'price' => 50]]);
    expect($servicePicker->getState())->toBeNull();
    $itemPicker->state(5);
    $itemPicker->getAction('addDetails')->call(['data' => ['catalogue_id' => 5, 'quantity' => 3, 'price' => 100]]);

    expect($livewire->data['items'])->toHaveCount(3)
        ->and($livewire->data['amount'])->toBe(550.0)
        ->and($livewire->data['credit_balance'])->toBe(550.0);
});

it('renders editable job rows as a table with the current line total', function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    Illuminate\Support\Facades\Schema::create('items', function ($table) {
        $table->id();
        $table->string('name');
        $table->decimal('selling_price', 12, 2);
        $table->softDeletes();
    });
    Illuminate\Support\Facades\Schema::create('payments', function ($table) {
        $table->id();
        $table->unsignedBigInteger('invoice_id')->nullable();
        $table->decimal('amount_paid', 12, 2);
        $table->boolean('discount_available')->default(false);
        $table->decimal('discount', 12, 2)->default(0);
    });
    Illuminate\Support\Facades\DB::table('items')->insert(['id' => 1, 'name' => 'Table test part', 'selling_price' => 150]);
    Livewire\Livewire::test(InvoiceItemsTablePreview::class, ['rows' => ['new' => [
        'item_id' => 1, 'service_id' => null, 'is_item' => true, 'is_service' => false,
        'quantity' => 2, 'price' => 150, 'warranty_available' => false, 'warranty_type' => null,
    ]]])->assertSeeHtml('<table')->assertSee('Item / service')->assertSee('Total (LKR)')
        ->assertSee('300.00')->assertSeeHtml('data.items.new.quantity')->assertSeeHtml('data.items.new.price')
        ->set('data.items.new.quantity', 3)->assertSee('450.00')->assertSet('data.amount', 450.0)
        ->call('mountFormComponentAction', 'data.items', 'delete', ['item' => 'new'])
        ->assertSet('data.items', [])->assertSet('data.amount', 0.0)->assertSee('No items or services added.');
});

it('recalculates mixed rows with the correct parent record at different invoice sizes', function (int $count, bool $existing) {
    $invoice = null;
    $rows = [];
    $records = new Collection;
    $expected = 0.0;
    for ($i = 1; $i <= $count; $i++) {
        $service = $i % 2 === 0;
        $row = ['is_service' => $service, 'is_item' => ! $service, 'quantity' => $service ? 1 : 2, 'price' => 10];
        $rows[($existing ? 'record-' : 'new-').$i] = $row;
        $record = new InvoiceItem($row);
        $record->id = $i;
        $records->push($record);
        $expected += $service ? 10 : 20;
    }
    if ($existing) {
        $invoice = Mockery::mock(Invoice::class)->makePartial();
        $invoice->setRelation('invoiceItems', $records);
        $payments = Mockery::mock(HasMany::class);
        $payments->shouldReceive('get')->andReturn(new Collection);
        $invoice->shouldReceive('payments')->andReturn($payments);
    }
    [$livewire, $form] = invoiceResourceForm($invoice);
    $livewire->data = ['items' => $rows];
    $prefix = $existing ? 'record-' : 'new-';
    foreach (array_unique([1, (int) ceil($count / 2), $count]) as $i) {
        $key = $prefix.$i;
        $quantity = $livewire->data['items'][$key]['quantity'];
        $livewire->data['items'][$key]['price'] = 25;
        $expected += $quantity * 15;
        $form->callAfterStateUpdated('data.items.'.$key.'.price');
        expect($livewire->data['amount'])->toBe($expected)
            ->and($livewire->data['credit_balance'])->toBe($expected);
        if (! $livewire->data['items'][$key]['is_service']) {
            $livewire->data['items'][$key]['quantity'] = 3;
            $expected += 25;
            $form->callAfterStateUpdated('data.items.'.$key.'.quantity');
            expect($livewire->data['amount'])->toBe($expected);
        }
    }
    unset($livewire->data['items'][$prefix.'1']);
    $form->callAfterStateUpdated('data.items');
    expect($livewire->data['amount'])->toBe($expected - 75);
})->with([1, 10, 100, 500])->with([false, true]);