<x-filament-panels::page>
@php
$groups = $this->getGroupedInvoiceItems();

// Optional date filter
$start = request('start_date');
$end = request('end_date');

// FIXED total calculation (grouped arrays!)
$totalServices = collect($groups['services'])
    ->sum(fn($record) => $record['total']);

$totalItems = collect($groups['items'])
    ->sum(fn($record) => $record['total']);

$grandTotal = $totalServices + $totalItems + $groups['partial_payments_total'];
@endphp

<div class="bg-white shadow dark:bg-gray-800 rounded-lg p-4 mt-10">

    {{-- Center Date Picker --}}
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
    <div class="overflow-hidden rounded-xl border">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-black dark:text-white">Name</th>
                    <th class="px-4 py-2 text-right text-sm font-medium text-black dark:text-white">Total (LKR)</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                {{-- SERVICES HEADER --}}
                <tr class="cursor-pointer font-semibold bg-gray-100 dark:bg-gray-800"
                    onclick="toggleGroup('services-group')">
                    <td class="px-4 py-2">Services <i class="fas fa-chevron-down"></i></td>
                    <td class="px-4 py-2 text-right">{{ number_format($totalServices, 2) }}</td>
                </tr>

                {{-- SERVICES LIST (NO QUANTITY) --}}
                @foreach($groups['services'] as $record)
                <tr class="hidden services-group bg-white dark:bg-gray-800">
                    <td class="px-6 py-2 text-sm">
                        {{ $record['name'] }}
                    </td>
                    <td class="px-4 py-2 text-sm text-right">
                        {{ number_format($record['total'], 2) }}
                    </td>
                </tr>
                @endforeach

                {{-- ITEMS HEADER --}}
                <tr class="cursor-pointer font-semibold bg-gray-100 dark:bg-gray-800"
                    onclick="toggleGroup('items-group')">
                    <td class="px-4 py-2">Items <i class="fas fa-chevron-down"></i></td>
                    <td class="px-4 py-2 text-right">{{ number_format($totalItems, 2) }}</td>
                </tr>

                {{-- ITEMS LIST (SHOW QUANTITY) --}}
                @foreach($groups['items'] as $record)
                <tr class="hidden items-group bg-white dark:bg-gray-800">
                    <td class="px-6 py-2 text-sm">
                        {{ $record['name'] }} (Qty: {{ $record['quantity'] }})
                    </td>
                    <td class="px-4 py-2 text-sm text-right">
                        {{ number_format($record['total'], 2) }}
                    </td>
                </tr>
                @endforeach

                {{-- PARTIAL PAYMENTS --}}
                <tr class="font-semibold bg-gray-100 dark:bg-gray-800">
                    <td class="px-4 py-2">Partial Payments</td>
                    <td class="px-4 py-2 text-right">{{ number_format($groups['partial_payments_total'], 2) }}</td>
                </tr>

                {{-- GRAND TOTAL --}}
                <tr class="font-bold bg-gray-200 dark:bg-gray-700">
                    <td class="px-4 py-2">Total Income</td>
                    <td class="px-4 py-2 text-right">{{ number_format($grandTotal, 2) }}</td>
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
        document.querySelectorAll('.' + className).forEach(row => {
            row.classList.toggle('hidden');
        });
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
        window.location.href = window.location.pathname; // removes all ?query parameters
    }
</script>
</x-filament-panels::page>
