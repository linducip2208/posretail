<x-filament-panels::page>
    <x-filament-schemas::form wire:submit="save">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Simpan Pengaturan
        </x-filament::button>
    </x-filament-schemas::form>
</x-filament-panels::page>
