<?php

namespace App\Filamemt\Resources\InvoiceResource\Widgets\TodayInvIncomeWidget;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Flowframe\Trend\Trend;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Invoice;

class TodayInvIncomeWidget extends BaseWidget
{
    protected static string $view = 'filament-widgets::stats-overview-widget';

    protected function getColumns(): int
    {
        return 2; // Adjust the number of columns as needed
    }

    protected function getStats(): array
    {
        // Query for today's invoices
        $invoiceQuery = Invoice::query();

        $invoiceTypeQuery = (clone $invoiceQuery)->where('is_invoice', true);

        // Get the trend data for the last 30 days
        $trend = Trend::query((clone $invoiceQuery))
            ->interval('day')
            ->dateColumn('created_at')
            ->between(now()->subDays(30), now())
            ->sum('amount');

        $trendc = Trend::query((clone $invoiceTypeQuery))
            ->interval('day')
            ->dateColumn('created_at')
            ->between(now()->subDays(30), now())
            ->count();

        return [
            Stat::make('Invoices Today', (clone $invoiceTypeQuery)->whereDate('created_at', Carbon::today())->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->value((clone $invoiceQuery)->whereDate('created_at', Carbon::today())->where('is_invoice', true)->count())
                ->chart($trendc->pluck('aggregate')->toArray())
                ->color('primary')
                ->icon('heroicon-s-shopping-cart'),

            Stat::make('Total Income Today', number_format((clone $invoiceQuery)->whereDate('created_at', Carbon::today())->sum('amount'), 2))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->value(number_format((clone $invoiceQuery)->whereDate('created_at', Carbon::today())->sum('amount'), 2))
                ->chart($trend->pluck('aggregate')->toArray())
                ->color('info')
                ->icon('heroicon-s-currency-dollar'),
        ];
    }
}
