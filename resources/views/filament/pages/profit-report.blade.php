<x-filament-panels::page>
@php
$groups = $this->getProfitData();

// Totals for services and items
$totalServices = collect($groups['services'])->sum('total');
$totalItems    = collect($groups['items'])->sum('total');

// Grand total income
$grandTotal = $groups['income_total'];
@endphp

<div class="bg-white shadow dark:bg-gray-800 rounded-lg p-4 mt-10">

    <div class="mb-24 flex justify-center">
        <div class="flex items-center space-x-4 border rounded-lg px-4 py-2 dark:border-white">

            {{-- Start Date --}}
            <div class="relative border rounded">
                <input id="start_date" type="text" placeholder="Start Date" value="{{ request('start_date') }}"
                    class="pr-8 pl-2 py-1 text-black bg-transparent focus:ring-0 focus:outline-none" />
            </div>

            {{-- End Date --}}
            <div class="relative border rounded">
                <input id="end_date" type="text" placeholder="End Date" value="{{ request('end_date') }}"
                    class="pr-8 pl-2 py-1 text-black bg-transparent focus:ring-0 focus:outline-none" />
            </div>

            {{-- Filter Button --}}
            <button onclick="filterByDate()" class="filter-btn">
                Filter
            </button>

            {{-- Clear Button --}}
            <button onclick="clearFilter()" class="clear-btn">
                Clear
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-xl border">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-black dark:text-white">Date</th>
                    <th class="px-4 py-2 text-left font-medium text-black dark:text-white">Details</th>
                    <th class="px-4 py-2 text-center font-medium text-black dark:text-white">Qty</th>
                    <th class="px-4 py-2 text-right font-medium text-black dark:text-white">Income (LKR)</th>
                    <th class="px-4 py-2 text-right font-medium text-black dark:text-white">Expenses (LKR)</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                {{-- =========================
                     INCOME: Services
                ========================= --}}
                @if($groups['services']->count() > 0)
                <tr class="bg-gray-100 dark:bg-gray-700 cursor-pointer">
                    <td colspan="5" class="px-4 py-2 font-bold flex items-center justify-between">
                        Services
                    </td>
                </tr>
                @foreach($groups['services'] as $service)
                <tr class="bg-white dark:bg-gray-800">
                    <td class="px-4 py-2 text-sm">{{ $service['created_at'] }}</td>
                    <td class="px-4 py-2 text-sm">{{ $service['name'] }}</td>
                    <td class="px-4 py-2 text-center text-sm">-</td>
                    <td class="px-4 py-2 text-sm text-right">{{ number_format($service['total'],2) }}</td>
                    <td class="px-4 py-2 text-sm text-right">-</td>
                </tr>
                @endforeach
                @endif

                {{-- INCOME: Items --}}
                @if($groups['items']->count() > 0)
                <tr class="bg-gray-100 dark:bg-gray-700 cursor-pointer">
                    <td colspan="5" class="px-4 py-2 font-bold flex items-center justify-between">
                        Items
                    </td>
                </tr>
                @foreach($groups['items'] as $item)
                <tr class="bg-white dark:bg-gray-800">
                    <td class="px-4 py-2 text-sm">{{ $item['created_at'] }}</td>
                    <td class="px-4 py-2 text-sm">{{ $item['name'] }}</td>
                    <td class="px-4 py-2 text-center text-sm">{{ $item['quantity'] }}</td>
                    <td class="px-4 py-2 text-sm text-right">{{ number_format($item['total'],2) }}</td>
                    <td class="px-4 py-2 text-sm text-right">-</td>
                </tr>
                @endforeach
                @endif

                {{-- INCOME: Partial Payments --}}
                @if($groups['partial_payments']->count() > 0)
                <tr class="bg-gray-100 dark:bg-gray-700 cursor-pointer">
                    <td colspan="5" class="px-4 py-2 font-bold flex items-center justify-between">
                        Partial Payments
                    </td>
                </tr>
                @foreach($groups['partial_payments'] as $payment)
                <tr class="bg-white dark:bg-gray-800">
                    <td class="px-4 py-2 text-sm">{{ $payment['payment_date'] }}</td>
                    <td class="px-4 py-2 text-sm">Invoice No: {{ $payment['invoice_id'] }}</td>
                    <td class="px-4 py-2 text-center text-sm">-</td>
                    <td class="px-4 py-2 text-sm text-right">{{ number_format($payment['amount_paid'],2) }}</td>
                    <td class="px-4 py-2 text-sm text-right">-</td>
                </tr>
                @endforeach
                @endif

                {{-- =========================
                     EXPENSES: Procurements
                ========================= --}}
                @if($groups['procurements']->count() > 0)
                <tr class="bg-gray-100 dark:bg-gray-700 cursor-pointer">
                    <td colspan="5" class="px-4 py-2 font-bold flex items-center justify-between">
                        Procurements
                    </td>
                </tr>
                @foreach($groups['procurements'] as $proc)
                <tr class="bg-white dark:bg-gray-800">
                    <td class="px-4 py-2 text-sm">{{ $proc['created_at'] }}</td>
                    <td class="px-4 py-2 text-sm">{{ $proc['name'] }}</td>
                    <td class="px-4 py-2 text-center text-sm">{{ $proc['quantity'] }}</td>
                    <td class="px-4 py-2 text-sm text-right">-</td>
                    <td class="px-4 py-2 text-sm text-right">{{ number_format($proc['total'],2) }}</td>
                </tr>
                @endforeach
                @endif

                {{-- =========================
                     EXPENSES: Salaries

                {{-- EXPENSES: Supplies --}}
                @if($groups['supplies']->count() > 0)
                <tr class="bg-gray-100 dark:bg-gray-700 cursor-pointer">
                    <td colspan="5" class="px-4 py-2 font-bold flex items-center justify-between">
                        Supplies
                    </td>
                </tr>
                @foreach($groups['supplies'] as $supply)
                <tr class="bg-white dark:bg-gray-800">
                    <td class="px-4 py-2 text-sm">{{ $supply['created_at'] }}</td>
                    <td class="px-4 py-2 text-sm">{{ $supply['name'] }}</td>
                    <td class="px-4 py-2 text-center text-sm">{{ $supply['quantity'] }}</td>
                    <td class="px-4 py-2 text-sm text-right">-</td>
                    <td class="px-4 py-2 text-sm text-right">{{ number_format($supply['total'],2) }}</td>
                </tr>
                @endforeach
                @endif  

                {{-- =========================
                     TOTALS ROW
                ========================= --}}
                <tr class="font-bold bg-gray-200 dark:bg-gray-700">
                    <td colspan="3" class="px-4 py-2 text-left">Totals</td>
                    <td class="px-4 py-2 text-right">
                        {{ number_format($groups['income_total'],2) }}
                    </td>
                    <td class="px-4 py-2 text-right">
                        {{ number_format($groups['expense_total'],2) }}
                    </td>
                </tr>

                {{-- NET PROFIT ROW --}}
                <tr class="font-bold bg-green-100 dark:bg-green-800">
                    <td colspan="3" class="px-4 py-2 text-left">Net Profit</td>
                    <td colspan="2" class="px-4 py-2 text-right">
                        {{ number_format($groups['profit'],2) }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

{{-- Scripts --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .filter-btn {
        background-color: #007bff;
        color: white;
        padding: 8px 20px;
        border: 1px solid #007bff;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.25s ease-in-out;
    }

    .filter-btn:hover {
        background-color: #0069d9;
        border-color: #0062cc;
    }

    .clear-btn {
        background-color: #6c757d;
        color: white;
        padding: 8px 20px;
        border: 1px solid #6c757d;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.25s ease-in-out;
    }

    .clear-btn:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#start_date", { dateFormat: "Y-m-d", maxDate: "today" });
    flatpickr("#end_date", { dateFormat: "Y-m-d", maxDate: "today" });

    function toggleGroup(className) {
        const rows = document.querySelectorAll('.' + className);
        const icon = document.getElementById(className.replace('-row','') + '-icon');

        rows.forEach(row => row.classList.toggle('hidden'));

        if(icon) {
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }
    }

    function filterByDate() {
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;

        if (!start || !end) {
            alert('Please select both start and end dates');
            return;
        }

        window.location.href = `?start_date=${start}&end_date=${end}`;
    }

    function clearFilter() {
        window.location.href = window.location.pathname; // removes all query parameters
    }
</script>
</x-filament-panels::page>
