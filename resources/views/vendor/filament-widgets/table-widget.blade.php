@php
    $widgetMod = 'fi-wi-mod-' . \Illuminate\Support\Str::kebab((new \ReflectionClass($this))->getShortName());
@endphp
<x-filament-widgets::widget class="fi-wi-table {{ $widgetMod }}">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_START, scopes: static::class) }}

    {{ $this->table ?? null }}

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_END, scopes: static::class) }}
</x-filament-widgets::widget>
