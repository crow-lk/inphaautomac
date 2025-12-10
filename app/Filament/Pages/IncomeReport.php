<?php

namespace App\Filament\Pages;

use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Invoice;
use Filament\Pages\Page;

class IncomeReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.income-report';

    public function getGroupedInvoiceItems()
    {
        $start = request('start_date');
        $end = request('end_date');

        $query = InvoiceItem::query()
            ->whereHas('invoice', fn($q) => $q->where('payment_status', 'Paid'))
            ->with(['service', 'item', 'invoice'])
            ->orderByDesc('created_at');

        if ($start && $end) {
            $query->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        }

        $items = $query->get();

        // Get partial payment invoices first
        $partialInvoiceIds = Invoice::where('payment_status', 'Partial Paid')
            ->pluck('id');

        // Get payments for those invoices
        $partialPaymentsQuery = Payment::whereIn('invoice_id', $partialInvoiceIds);

        // Apply date filter (payment_date)
        if ($start && $end) {
            $partialPaymentsQuery->whereBetween('payment_date', [$start, $end]);
        }

        // Sum of actual partial payments
        $partialPaymentsTotal = $partialPaymentsQuery->sum('amount_paid');

        return [
        // Services grouped but without quantity
        'services' => $items->filter(fn($item) => $item->is_service)
            ->groupBy(fn($item) => $item->service->name ?? 'Service')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->service->name ?? 'Other Services',
                    'total' => $group->sum(fn($i) => $i->quantity * $i->price),
                ];
            })->values(),

        // Items grouped WITH quantity
        'items' => $items->filter(fn($item) => $item->is_item)
            ->groupBy(fn($item) => $item->item->name ?? 'Item')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->item->name ?? 'Other Items',
                    'quantity' => $group->sum('quantity'),
                    'total' => $group->sum(fn($i) => $i->quantity * $i->price),
                ];
            })->values(),

        'partial_payments_total' => $partialPaymentsTotal,
    ];


    }

}
