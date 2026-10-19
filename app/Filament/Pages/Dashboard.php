<?php

namespace App\Filament\Pages;

use App\Filament\Resources\InvoiceResource\Widgets\TodayInvIncomeWidget;
use App\Filament\Widgets\FinanceOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    public function getBmsWidgets(): array
    {
        return $this->filterVisibleWidgets(array_values(array_filter(
            parent::getWidgets(),
            fn ($widget) => ! in_array($widget, [TodayInvIncomeWidget::class, FinanceOverview::class], true),
        )));
    }

    public function getFinanceWidgets(): array
    {
        return $this->filterVisibleWidgets([TodayInvIncomeWidget::class, FinanceOverview::class]);
    }
}
