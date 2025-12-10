<?php

namespace App\Filament\Pages;

use App\Models\Procurement;
use App\Models\Supply;
use Filament\Pages\Page;

class ExpenseReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.expense-report';

    public function getExpenseData()
    {
        $start = request('start_date');
        $end   = request('end_date');

        $procurementQuery = Procurement::query()
            ->with('item')
            ->orderByDesc('created_at');

        if ($start && $end) {
            $procurementQuery->whereBetween('created_at', [
                $start . ' 00:00:00', 
                $end . ' 23:59:59'
            ]);
        }

        $procurements = $procurementQuery->get();

        // GROUP BY ITEM NAME
        $groupedProcurements = $procurements
            ->groupBy(fn($p) => $p->item->name ?? 'Unknown Item')
            ->map(function ($group) {
                return [
                    'name'     => $group->first()->item->name ?? 'Unknown Item',
                    'quantity' => $group->sum('qty'),
                    'total'    => $group->sum('totalcost'),
                ];
            })
            ->values();

        $supplyQuery = Supply::query()
            ->orderByDesc('created_at');

        if ($start && $end) {
            $supplyQuery->whereBetween('created_at', [
                $start . ' 00:00:00', 
                $end . ' 23:59:59'
            ]);
        }

        $supplies = $supplyQuery->get();

        // GROUP BY ITEM NAME
        $groupedSupplies = $supplies
            ->groupBy('item_name')
            ->map(function ($group) {
                return [
                    'name'     => $group->first()->item_name,
                    'quantity' => $group->sum('qty'),
                    'total'    => $group->sum('total'),
                ];
            })
            ->values();

        return [
            'procurements' => $groupedProcurements,
            'supplies'     => $groupedSupplies,

            'procurement_total' => $groupedProcurements->sum('total'),
            'supply_total'      => $groupedSupplies->sum('total'),
        ];
    }
}

