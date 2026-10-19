<x-filament-panels::page class="fi-dashboard-page">
    <x-filament::section heading="BMS">
        <x-filament-widgets::widgets
            :columns="$this->getColumns()"
            :data="$this->getWidgetData()"
            :widgets="$this->getBmsWidgets()"
        />
    </x-filament::section>

    <x-filament::section heading="Finance">
        <x-filament-widgets::widgets
            :columns="$this->getColumns()"
            :data="$this->getWidgetData()"
            :widgets="$this->getFinanceWidgets()"
        />
    </x-filament::section>
</x-filament-panels::page>
