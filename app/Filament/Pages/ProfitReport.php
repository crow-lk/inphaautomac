<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Procurement;
use App\Models\Supply;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Payment;

class ProfitReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.profit-report';

    public function getProfitData(): array
    {
        $start = request('start_date');
        $end   = request('end_date');

        // Procurements
        $procurementQuery = Procurement::query()->with('item');

        if ($start && $end) {
            $procurementQuery->whereBetween('created_at', [
                $start . ' 00:00:00',
                $end . ' 23:59:59',
            ]);
        }

        $procurements = $procurementQuery->get();

        $groupedProcurements = $procurements
            ->groupBy(fn ($p) => $p->item->name ?? 'Unknown Item')
            ->map(fn ($group) => [
                'name'     => $group->first()->item->name ?? 'Unknown Item',
                'quantity' => $group->sum('qty'),
                'total'    => $group->sum('totalcost'),
                'created_at' => $group->first()->created_at->format('Y-m-d'),
            ])->values();

        // Supplies
        $supplyQuery = Supply::query();

        if ($start && $end) {
            $supplyQuery->whereBetween('created_at', [
                $start . ' 00:00:00',
                $end . ' 23:59:59',
            ]);
        }

        $supplies = $supplyQuery->get();

        $groupedSupplies = $supplies
            ->groupBy('item_name')
            ->map(fn ($group) => [
                'name'     => $group->first()->item_name,
                'quantity' => $group->sum('qty'),
                'total'    => $group->sum('total'),
                'created_at' => $group->first()->created_at->format('Y-m-d'),
            ])->values();

        $expenseTotal =
            $groupedProcurements->sum('total')
            + $groupedSupplies->sum('total');

        $invoiceItemQuery = InvoiceItem::query()
            ->whereHas('invoice', fn ($q) => $q->where('payment_status', 'Paid'))
            ->with(['service', 'item']);

        if ($start && $end) {
            $invoiceItemQuery->whereBetween('created_at', [
                $start . ' 00:00:00',
                $end . ' 23:59:59',
            ]);
        }

        $items = $invoiceItemQuery->get();

        // Services income (no quantity)
        $services = $items->filter(fn ($i) => $i->is_service)
            ->groupBy(fn ($i) => $i->service->name ?? 'Service')
            ->map(fn ($group) => [
                'name'  => $group->first()->service->name ?? 'Other Services',
                'total' => $group->sum(fn ($i) => $i->quantity * $i->price),
                'created_at' => $group->first()->created_at->format('Y-m-d'),
            ])->values();

        // Items income (with quantity)
        $products = $items->filter(fn ($i) => $i->is_item)
            ->groupBy(fn ($i) => $i->item->name ?? 'Item')
            ->map(fn ($group) => [
                'name'     => $group->first()->item->name ?? 'Other Items',
                'quantity' => $group->sum('quantity'),
                'total'    => $group->sum(fn ($i) => $i->quantity * $i->price),
                'created_at' => $group->first()->created_at->format('Y-m-d'),
            ])->values();

        // Partial payments
        $partialInvoiceIds = Invoice::where('payment_status', 'Partial Paid')
            ->pluck('id');

        $partialPaymentQuery = Payment::whereIn('invoice_id', $partialInvoiceIds);

        if ($start && $end) {
            $partialPaymentQuery->whereBetween('payment_date', [$start, $end]);
        }

        $partialPayments = $partialPaymentQuery->get();

        // Map to array with date and amount
        $partialPaymentsData = $partialPayments->map(fn($p) => [
            'invoice_id'   => $p->invoice_id,
            'payment_date' => $p->payment_date->format('Y-m-d'),
            'amount_paid'  => $p->amount_paid,
        ])->values();

        // Total for summary
        $partialPaymentsTotal = $partialPaymentsData->sum('amount_paid');

        $incomeTotal =
            $services->sum('total')
            + $products->sum('total')
            + $partialPaymentsTotal;


        return [
            // EXPENSES
            'procurements'        => $groupedProcurements,
            'supplies'            => $groupedSupplies,
            'expense_total'       => $expenseTotal,

            // INCOME
            'services'            => $services,
            'items'               => $products,
            'partial_payments'    => $partialPaymentsData,
            'partial_payments_total' => $partialPaymentsTotal,
            'income_total'        => $incomeTotal,

            // PROFIT
            'profit'              => $incomeTotal - $expenseTotal,
        ];
    }
}
