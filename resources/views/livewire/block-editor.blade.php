<div
    x-data="{
        dragging: null,
        dragOver: null,
        showPalette: @entangle('showPalette'),
        selectedBlockId: @entangle('selectedBlockId'),

        startDrag(blockId) {
            this.dragging = blockId;
        },
        onDragOver(e, blockId) {
            e.preventDefault();
            this.dragOver = blockId;
        },
        onDrop(e, targetId) {
            e.preventDefault();
            if (this.dragging && this.dragging !== targetId) {
                let ids = @js(collect($blocks)->pluck('id')->toArray());
                let fromIdx = ids.indexOf(this.dragging);
                let toIdx = ids.indexOf(targetId);
                if (fromIdx > -1 && toIdx > -1) {
                    ids.splice(fromIdx, 1);
                    ids.splice(toIdx, 0, this.dragging);
                    $wire.reorderBlocks(ids);
                }
            }
            this.dragging = null;
            this.dragOver = null;
        },
        endDrag() {
            this.dragging = null;
            this.dragOver = null;
        }
    }"
    class="flex h-screen bg-gray-100 overflow-hidden"
    x-on:keydown.escape.window="selectedBlockId = null; $wire.selectBlock(null)"
>
    {{-- ============================================ --}}
    {{-- LEFT PANEL: Block Palette --}}
    {{-- ============================================ --}}
    <aside
        x-show="showPalette"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        class="w-64 bg-white border-r border-gray-200 flex flex-col shadow-sm flex-shrink-0 overflow-hidden"
    >
        <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-white">
            <h3 class="text-sm font-bold text-gray-700 tracking-tight">Bloques disponibles</h3>
            <p class="text-xs text-gray-400 mt-0.5">Clic para añadir al documento</p>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-4">
            @foreach($blockPalette as $category => $categoryBlocks)
                <div>
                    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">
                        {{ $categoryLabels[$category] ?? $category }}
                    </h4>
                    <div class="space-y-1">
                        @foreach($categoryBlocks as $blockDef)
                            <button
                                wire:click="addBlock('{{ $blockDef['type'] }}')"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition-all
                                       hover:bg-amber-50 hover:shadow-sm active:scale-[0.98]
                                       border border-transparent hover:border-amber-200 group"
                            >
                                <span class="flex items-center justify-center w-8 h-8 rounded-md bg-gray-50 group-hover:bg-amber-100 transition-colors">
                                    <x-dynamic-component :component="'heroicon-o-' . $blockDef['icon']" class="w-4 h-4 text-gray-500 group-hover:text-amber-600" />
                                </span>
                                <span class="text-sm text-gray-600 group-hover:text-gray-800 font-medium">{{ $blockDef['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </aside>

    {{-- ============================================ --}}
    {{-- CENTER: A4 Preview Canvas --}}
    {{-- ============================================ --}}
    <main class="flex-1 flex flex-col overflow-hidden">
        {{-- Toolbar --}}
        <div class="bg-white border-b border-gray-200 px-4 py-2 flex items-center justify-between shadow-sm flex-shrink-0">
            <div class="flex items-center gap-3">
                <button
                    @click="showPalette = !showPalette"
                    class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    title="Toggle palette"
                >
                    <x-heroicon-o-squares-plus class="w-5 h-5 text-gray-500" />
                </button>

                <div class="h-6 w-px bg-gray-200"></div>

                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-700">{{ $templateName ?: 'Nuevo Template' }}</span>
                    @if($unsavedChanges)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                            Sin guardar
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <select
                    wire:model.live="templateStatus"
                    class="text-sm border-gray-200 rounded-lg py-1.5 px-3 focus:ring-amber-500 focus:border-amber-500"
                >
                    <option value="draft">Borrador</option>
                    <option value="active">Activo</option>
                    <option value="deprecated">Obsoleto</option>
                </select>

                <button
                    wire:click="save"
                    @class([
                        'inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all',
                        'bg-amber-500 text-white hover:bg-amber-600 shadow-sm' => $unsavedChanges,
                        'bg-gray-100 text-gray-400 cursor-default' => !$unsavedChanges,
                    ])
                    @if(!$unsavedChanges && $templateVersionId) disabled @endif
                >
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    Guardar
                </button>
            </div>
        </div>

        {{-- Canvas Area --}}
        <div class="flex-1 overflow-y-auto bg-gray-200/50 p-6">
            <div class="mx-auto" style="width: 794px;">
                {{-- A4 Paper --}}
                <div
                    class="bg-white shadow-xl rounded-sm border border-gray-300 relative"
                    style="
                        min-height: 1123px;
                        padding: {{ $pageConfig['margins']['top'] ?? 20 }}px {{ $pageConfig['margins']['right'] ?? 15 }}px {{ $pageConfig['margins']['bottom'] ?? 20 }}px {{ $pageConfig['margins']['left'] ?? 15 }}px;
                        font-size: {{ $pageConfig['fontSize'] ?? 10 }}px;
                    "
                >
                    {{-- Page size indicator --}}
                    <div class="absolute top-2 right-2 text-[10px] text-gray-300 pointer-events-none select-none">
                        A4 (210 x 297 mm)
                    </div>

                    @if(empty($blocks))
                        {{-- Empty state --}}
                        <div class="flex flex-col items-center justify-center py-32 text-center">
                            <x-heroicon-o-document-plus class="w-16 h-16 text-gray-200 mb-4" />
                            <h3 class="text-lg font-medium text-gray-400">Documento vacío</h3>
                            <p class="text-sm text-gray-300 mt-1 max-w-xs">
                                Añade bloques desde la paleta lateral para empezar a construir tu plantilla de informe
                            </p>
                        </div>
                    @else
                        {{-- Render blocks --}}
                        @foreach($blocks as $index => $block)
                            <div
                                wire:key="block-{{ $block['id'] }}"
                                draggable="true"
                                x-on:dragstart="startDrag('{{ $block['id'] }}')"
                                x-on:dragover="onDragOver($event, '{{ $block['id'] }}')"
                                x-on:drop="onDrop($event, '{{ $block['id'] }}')"
                                x-on:dragend="endDrag()"
                                @class([
                                    'group relative transition-all duration-150 cursor-pointer',
                                    'ring-2 ring-amber-400 ring-offset-1 rounded-sm' => $selectedBlockId === $block['id'],
                                    'hover:ring-1 hover:ring-gray-300 hover:ring-offset-1 rounded-sm' => $selectedBlockId !== $block['id'],
                                    'opacity-50' => false,
                                ])
                                :class="{
                                    'border-t-2 border-amber-400': dragOver === '{{ $block['id'] }}' && dragging !== '{{ $block['id'] }}'
                                }"
                                wire:click="selectBlock('{{ $block['id'] }}')"
                            >
                                {{-- Block actions overlay --}}
                                <div class="absolute -left-10 top-0 bottom-0 flex flex-col items-center justify-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        wire:click.stop="moveBlockUp('{{ $block['id'] }}')"
                                        class="p-1 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600"
                                        title="Mover arriba"
                                        @if($index === 0) disabled @endif
                                    >
                                        <x-heroicon-m-chevron-up class="w-3.5 h-3.5" />
                                    </button>
                                    <div class="w-5 h-5 flex items-center justify-center cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500">
                                        <x-heroicon-m-bars-2 class="w-4 h-4" />
                                    </div>
                                    <button
                                        wire:click.stop="moveBlockDown('{{ $block['id'] }}')"
                                        class="p-1 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600"
                                        title="Mover abajo"
                                        @if($index === count($blocks) - 1) disabled @endif
                                    >
                                        <x-heroicon-m-chevron-down class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                {{-- Quick actions (top-right) --}}
                                <div class="absolute -right-10 top-0 flex flex-col gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        wire:click.stop="duplicateBlock('{{ $block['id'] }}')"
                                        class="p-1 rounded hover:bg-blue-50 text-gray-400 hover:text-blue-500"
                                        title="Duplicar"
                                    >
                                        <x-heroicon-m-document-duplicate class="w-3.5 h-3.5" />
                                    </button>
                                    <button
                                        wire:click.stop="removeBlock('{{ $block['id'] }}')"
                                        class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-500"
                                        title="Eliminar"
                                    >
                                        <x-heroicon-m-trash class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                {{-- Block type badge --}}
                                <div class="absolute -top-2.5 left-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-700 text-white shadow-sm">
                                        {{ $block['type'] }}
                                    </span>
                                </div>

                                {{-- Block preview content --}}
                                <div class="pointer-events-none">
                                    {!! \App\BlockEditor\BlockRegistry::renderPreview($block['type'], $block['config'] ?? []) !!}
                                </div>
                            </div>

                            {{-- Insert between blocks indicator --}}
                            <div
                                class="relative h-0 group/insert"
                                wire:key="insert-{{ $block['id'] }}"
                            >
                                <div class="absolute inset-x-0 -top-1 h-2 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity z-10">
                                    <button
                                        wire:click.stop="insertBlockAfterSelected('section_title')"
                                        x-on:click="$wire.selectBlock(null)"
                                        class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-medium shadow-md hover:bg-amber-600 transition-colors"
                                    >
                                        <x-heroicon-m-plus class="w-3 h-3" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </main>

    {{-- ============================================ --}}
    {{-- RIGHT PANEL: Block Configuration --}}
    {{-- ============================================ --}}
    <aside
        x-show="selectedBlockId !== null"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="w-80 bg-white border-l border-gray-200 flex flex-col shadow-sm flex-shrink-0 overflow-hidden"
    >
        @if($selectedBlock)
            {{-- Config header --}}
            <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-white flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-700">Configuración</h3>
                    <p class="text-xs text-gray-400">{{ $selectedBlock['type'] }}</p>
                </div>
                <button
                    wire:click="selectBlock(null)"
                    x-on:click="selectedBlockId = null"
                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>

            {{-- Config fields --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @foreach($configSchema as $field)
                    <div wire:key="config-{{ $field['key'] }}">
                        @switch($field['type'])
                            @case('text')
                                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ $field['label'] }}</label>
                                <input
                                    type="text"
                                    value="{{ $selectedBlock['config'][$field['key']] ?? '' }}"
                                    wire:change="updateBlockConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}', $event.target.value)"
                                    class="w-full text-sm border-gray-200 rounded-lg py-2 px-3 focus:ring-amber-500 focus:border-amber-500"
                                />
                                @break

                            @case('number')
                                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ $field['label'] }}</label>
                                <input
                                    type="number"
                                    value="{{ $selectedBlock['config'][$field['key']] ?? '' }}"
                                    wire:change="updateBlockConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}', Number($event.target.value))"
                                    class="w-full text-sm border-gray-200 rounded-lg py-2 px-3 focus:ring-amber-500 focus:border-amber-500"
                                />
                                @break

                            @case('toggle')
                                <label class="flex items-center justify-between cursor-pointer group">
                                    <span class="text-xs font-semibold text-gray-500 group-hover:text-gray-700">{{ $field['label'] }}</span>
                                    <button
                                        type="button"
                                        wire:click="updateBlockConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}', {{ ($selectedBlock['config'][$field['key']] ?? false) ? 'false' : 'true' }})"
                                        @class([
                                            'relative inline-flex h-5 w-9 items-center rounded-full transition-colors',
                                            'bg-amber-500' => ($selectedBlock['config'][$field['key']] ?? false),
                                            'bg-gray-200' => !($selectedBlock['config'][$field['key']] ?? false),
                                        ])
                                    >
                                        <span @class([
                                            'inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform shadow-sm',
                                            'translate-x-4.5' => ($selectedBlock['config'][$field['key']] ?? false),
                                            'translate-x-0.5' => !($selectedBlock['config'][$field['key']] ?? false),
                                        ])></span>
                                    </button>
                                </label>
                                @break

                            @case('select')
                                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ $field['label'] }}</label>
                                <select
                                    wire:change="updateBlockConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}', $event.target.value)"
                                    class="w-full text-sm border-gray-200 rounded-lg py-2 px-3 focus:ring-amber-500 focus:border-amber-500"
                                >
                                    @foreach(($field['options'] ?? []) as $value => $optionLabel)
                                        <option
                                            value="{{ $value }}"
                                            @selected(($selectedBlock['config'][$field['key']] ?? '') == $value)
                                        >{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                                @break

                            @case('color')
                                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ $field['label'] }}</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        value="{{ $selectedBlock['config'][$field['key']] ?? '#f59e0b' }}"
                                        wire:change="updateBlockConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}', $event.target.value)"
                                        class="h-9 w-12 rounded-lg border border-gray-200 cursor-pointer"
                                    />
                                    <input
                                        type="text"
                                        value="{{ $selectedBlock['config'][$field['key']] ?? '#f59e0b' }}"
                                        wire:change="updateBlockConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}', $event.target.value)"
                                        class="flex-1 text-sm border-gray-200 rounded-lg py-2 px-3 font-mono focus:ring-amber-500 focus:border-amber-500"
                                    />
                                </div>
                                @break

                            @case('key_value_list')
                                <label class="block text-xs font-semibold text-gray-500 mb-2">{{ $field['label'] }}</label>
                                @php
                                    $items = $selectedBlock['config'][$field['key']] ?? [];
                                @endphp
                                <div class="space-y-2 bg-gray-50 rounded-lg p-3">
                                    @foreach($items as $itemIdx => $item)
                                        <div class="flex items-center gap-2" wire:key="kv-{{ $field['key'] }}-{{ $itemIdx }}">
                                            <input
                                                type="text"
                                                value="{{ $item['value'] ?? $item['key'] ?? '' }}"
                                                placeholder="Valor"
                                                class="flex-1 text-xs border-gray-200 rounded py-1.5 px-2 focus:ring-amber-500 focus:border-amber-500"
                                                wire:change="updateNestedConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}.{{ $itemIdx }}.value', $event.target.value)"
                                            />
                                            <input
                                                type="text"
                                                value="{{ $item['label'] ?? '' }}"
                                                placeholder="Etiqueta"
                                                class="flex-1 text-xs border-gray-200 rounded py-1.5 px-2 focus:ring-amber-500 focus:border-amber-500"
                                                wire:change="updateNestedConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}.{{ $itemIdx }}.label', $event.target.value)"
                                            />
                                        </div>
                                    @endforeach
                                    <p class="text-[10px] text-gray-400 mt-1">Edita los valores directamente arriba</p>
                                </div>
                                @break

                            @case('table_columns')
                                <label class="block text-xs font-semibold text-gray-500 mb-2">{{ $field['label'] }}</label>
                                @php
                                    $cols = $selectedBlock['config'][$field['key']] ?? [];
                                @endphp
                                <div class="space-y-3 bg-gray-50 rounded-lg p-3">
                                    @foreach($cols as $colIdx => $col)
                                        <div class="p-2 bg-white rounded border border-gray-100" wire:key="tc-{{ $colIdx }}">
                                            <input
                                                type="text"
                                                value="{{ $col['label'] ?? '' }}"
                                                placeholder="Nombre columna"
                                                class="w-full text-xs border-gray-200 rounded py-1.5 px-2 mb-1 focus:ring-amber-500 focus:border-amber-500"
                                                wire:change="updateNestedConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}.{{ $colIdx }}.label', $event.target.value)"
                                            />
                                            <select
                                                class="w-full text-xs border-gray-200 rounded py-1.5 px-2 focus:ring-amber-500 focus:border-amber-500"
                                                wire:change="updateNestedConfig('{{ $selectedBlock['id'] }}', '{{ $field['key'] }}.{{ $colIdx }}.type', $event.target.value)"
                                            >
                                                <option value="text" @selected(($col['type'] ?? 'text') === 'text')>Texto</option>
                                                <option value="number" @selected(($col['type'] ?? 'text') === 'number')>Número</option>
                                                <option value="date" @selected(($col['type'] ?? 'text') === 'date')>Fecha</option>
                                                <option value="tristate" @selected(($col['type'] ?? 'text') === 'tristate')>OK/NOK/NA</option>
                                                <option value="select" @selected(($col['type'] ?? 'text') === 'select')>Selección</option>
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                                @break
                        @endswitch
                    </div>
                @endforeach

                {{-- Danger zone --}}
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <div class="flex gap-2">
                        <button
                            wire:click="duplicateBlock('{{ $selectedBlock['id'] }}')"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 transition-colors"
                        >
                            <x-heroicon-m-document-duplicate class="w-3.5 h-3.5" />
                            Duplicar
                        </button>
                        <button
                            wire:click="removeBlock('{{ $selectedBlock['id'] }}')"
                            wire:confirm="¿Eliminar este bloque?"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 transition-colors"
                        >
                            <x-heroicon-m-trash class="w-3.5 h-3.5" />
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                <x-heroicon-o-cursor-arrow-rays class="w-10 h-10 text-gray-200 mb-3" />
                <p class="text-sm text-gray-400">Selecciona un bloque para configurarlo</p>
            </div>
        @endif
    </aside>
</div>
