@php
    $containers = $getChildComponentContainers();
    $deleteAction = $getAction($getDeleteActionName());
    $confirmAction = $getExtraItemActions()['confirmPrice'];
    $livewireId = $getLivewire()->getId();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{}" {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-fo-repeater space-y-4']) }}>
        @forelse ($containers as $uuid => $item)
            @php
                $fields = collect($item->getComponents(withHidden: true))->keyBy(fn ($component) => $component->getName());
                $state = $item->getRawState();
                $quantity = ($state['is_service'] ?? false) ? 1 : (float) ($state['quantity'] ?? 0);
                $total = max(0, $quantity * (float) ($state['price'] ?? 0) - (float) ($state['discount'] ?? 0));
                $rowDeleteAction = $deleteAction(['item' => $uuid]);
            @endphp
            <section wire:key="{{ $livewireId }}.{{ $item->getStatePath() }}.form-card" class="rounded-xl bg-white p-4 ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/10" aria-label="{{ ($state['is_service'] ?? false) ? 'Service' : 'Item' }} {{ $loop->iteration }}">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ ($state['is_service'] ?? false) ? 'Service' : 'Item' }} {{ $loop->iteration }}
                    </h3>
                    @if ($isDeletable() && $rowDeleteAction->isVisible())
                        {{ $rowDeleteAction }}
                    @endif
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach (['item_id', 'service_id', 'quantity', 'price', 'discount', 'warranty_available', 'warranty_type'] as $name)
                        @if (isset($fields[$name]) && $fields[$name]->isVisible())
                            <div @class(['min-w-0', 'sm:col-span-2' => in_array($name, ['item_id', 'service_id'])])>
                                {{ $fields[$name] }}
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-white/10">
                    <div class="text-sm font-medium text-gray-950 dark:text-white">
                        Total (LKR): {{ number_format($total, 2) }}
                    </div>
                    <span x-show="['price', 'quantity', 'discount'].some(field => String($wire.$get(@js($item->getStatePath()))?.[field] ?? (field === 'discount' ? 0 : '')) !== String($wire.$get(@js($item->getStatePath()))?.confirmed_values?.[field] ?? (field === 'discount' ? 0 : '')))" style="display: none;">
                        {{ $confirmAction(['item' => $uuid]) }}
                    </span>
                </div>
            </section>
        @empty
            <div class="rounded-xl p-6 text-center text-sm text-gray-500 ring-1 ring-gray-950/10 dark:text-gray-400 dark:ring-white/10">
                No items or services added. Use the search boxes above to add them.
            </div>
        @endforelse
    </div>
</x-dynamic-component>
