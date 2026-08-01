<x-filament-panels::page>
    <form wire:submit="guardar" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" size="lg" icon="heroicon-o-check">
                Guardar cambios
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
