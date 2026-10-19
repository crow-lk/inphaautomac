<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ExpenseReport;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Procurement;
use App\Models\Supply;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = today();
        $start = $today->copy()->startOfMonth();
        $end = $today->copy()->endOfDay();
        $invoices = Invoice::query()->where('is_invoice', true);
        $payments = Payment::query()->whereHas('invoice', fn ($query) => $query->where('is_invoice', true));
        $receivedToday = (clone $payments)->whereDate('payment_date', $today)->sum('amount_paid');
        $receivedMonth = (clone $payments)->whereBetween('payment_date', [$start, $end])->sum('amount_paid');
        $invoicedMonth = (clone $invoices)->whereBetween('created_at', [$start, $end])->sum('amount');
        $expenses = Procurement::query()->whereBetween('created_at', [$start, $end])->sum('totalcost')
            + Supply::query()->whereBetween('created_at', [$start, $end])->sum('total');
        $outstanding = (clone $invoices)->where('credit_balance', '>', 0)->sum('credit_balance');
        $net = $receivedMonth - $expenses;
        $currency = fn ($amount) => 'LKR '.number_format((float) $amount, 2);

        return [
            Stat::make('Payments Received Today', $currency($receivedToday))
                ->description('Actual payments received today')
                ->icon('heroicon-o-banknotes')->color('success')->url(PaymentResource::getUrl()),
            Stat::make('Payments Received This Month', $currency($receivedMonth))
                ->description('Month to date, excluding discounts')
                ->icon('heroicon-o-currency-dollar')->color('success')->url(PaymentResource::getUrl()),
            Stat::make('Invoiced This Month', $currency($invoicedMonth))
                ->description('Invoices created this month, excluding quotations')
                ->icon('heroicon-o-document-text')->url(InvoiceResource::getUrl()),
            Stat::make('Expenses This Month', $currency($expenses))
                ->description('Month to date procurements and supplies')
                ->icon('heroicon-o-shopping-cart')->color('danger')->url(ExpenseReport::getUrl()),
            Stat::make('Net Cash Flow This Month', $currency($net))
                ->description('Payments received minus recorded expenses')
                ->icon('heroicon-o-arrows-right-left')->color($net >= 0 ? 'success' : 'danger'),
            Stat::make('Outstanding Invoice Balances', $currency($outstanding))
                ->description('Unpaid balances across all invoices')
                ->icon('heroicon-o-clock')->color('warning')->url(InvoiceResource::getUrl()),
        ];
    }
}
