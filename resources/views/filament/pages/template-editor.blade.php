<x-filament-panels::page>
    <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm" style="height: calc(100vh - 180px);">
        @livewire('block-editor', ['templateVersionId' => $this->templateVersionId])
    </div>
</x-filament-panels::page>
