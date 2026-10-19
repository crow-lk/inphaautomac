@php
    $containers = $getChildComponentContainers();
    $deleteAction = $getAction($getDeleteActionName());
    $livewireId = $getLivewire()->getId();
    $columns = [
        ['label' => 'Item / service', 'fields' => ['item_id', 'service_id', 'warranty_available', 'warranty_type'], 'width' => '600px'],
        ['label' => 'Quantity', 'fields' => ['quantity'], 'width' => '110px'],
        ['label' => 'Unit price', 'fields' => ['price'], 'width' => '160px'],
    ];
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{}" {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-fo-repeater']) }}>
        <div class="overflow-x-auto rounded-xl ring-1 ring-gray-950/10 dark:ring-white/10">
            <table class="w-full divide-y divide-gray-200 text-sm dark:divide-white/10" style="min-width: 1080px">
                <caption class="sr-only">Editable job items and services</caption>
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        @foreach ($columns as $column)
                            <th scope="col" class="px-3 py-3 text-start font-semibold text-gray-950 dark:text-white" style="min-width: {{ $column['width'] }}">
                                {{ $column['label'] }}
                            </th>
                        @endforeach
                        <th scope="col" class="px-3 py-3 text-end font-semibold text-gray-950 dark:text-white" style="min-width: 130px">Total (LKR)</th>
                        <th scope="col" class="px-3 py-3"><span class="sr-only">Remove row</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-white/5">
                    @forelse ($containers as $uuid => $item)
                        @php
                            $fields = collect($item->getComponents(withHidden: true))->keyBy(fn ($component) => $component->getName());
                            $state = $item->getRawState();
                            $quantity = ($state['is_service'] ?? false) ? 1 : (float) ($state['quantity'] ?? 0);
                            $total = $quantity * (float) ($state['price'] ?? 0);
                            $rowDeleteAction = $deleteAction(['item' => $uuid]);
                        @endphp
                        <tr wire:key="{{ $livewireId }}.{{ $item->getStatePath() }}.table-row">
                            @foreach ($columns as $column)
                                <td class="px-3 py-3 align-top">
                                    <div class="flex items-center gap-3" style="flex-wrap: nowrap;">
                                        @foreach ($column['fields'] as $name)
                                            @if (isset($fields[$name]) && $fields[$name]->isVisible())
                                                @php
                                                    $cellField = $fields[$name];
                                                    if (! $cellField instanceof \Filament\Forms\Components\Toggle) {
                                                        $cellField->hiddenLabel();
                                                    }
                                                @endphp
                                                <div style="{{ in_array($name, ['item_id', 'service_id']) ? 'flex: 1 1 260px; min-width: 220px;' : ($name === 'warranty_available' ? 'flex: 0 0 auto; white-space: nowrap;' : ($name === 'warranty_type' ? 'flex: 0 0 150px;' : 'width: 100%;')) }}">
                                                    {{ $cellField }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            @endforeach
                            <td class="px-3 py-3 text-end align-top font-medium text-gray-950 dark:text-white">
                                {{ number_format($total, 2) }}
                            </td>
                            <td class="px-3 py-3 align-top">
                                @if ($isDeletable() && $rowDeleteAction->isVisible())
                                    {{ $rowDeleteAction }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                No items or services added. Use the search boxes above to add them.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-dynamic-component>
