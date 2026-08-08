{{-- Livewire exige que la vista tenga un único elemento raíz -- el <style> tenía que ir
     ADENTRO del div raíz, no como hermano antes de él, si no Livewire pierde la referencia al
     componente y wire:click dejaba de encontrar los métodos (MethodNotFoundException en goTo).
     El panel de Filament sirve un CSS precompilado (vendor/filament/*/dist) que NO escanea
     resources/views/livewire/**, así que cualquier clase de Tailwind que no se use ya en algún
     lado del propio Filament (grid-cols-N, w-14, line-clamp-2, bg-primary-50, etc.) no existe en
     ese CSS y no hace nada -- se detectó porque las miniaturas se veían gigantes (el contenedor
     con "w-14 h-14" no tenía ningún ancho/alto real aplicado). Por eso este archivo usa CSS
     propio con estilos con scope (prefijo .fp-) en vez de clases de Tailwind para todo lo que
     no sea texto/colores básicos que Filament sí trae. --}}
<div class="space-y-4" wire:loading.class="opacity-60" x-data="{ selected: null }">
    <style>
        .fp-crumbs { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; font-size: 12px; padding: 0 2px; }
        .fp-crumbs button { display: inline-flex; align-items: center; gap: 4px; font-weight: 500; color: rgb(67 56 202); background: none; border: none; cursor: pointer; padding: 0; }
        .fp-crumbs button:hover { text-decoration: underline; }
        .fp-crumbs svg { width: 12px; height: 12px; color: #9ca3af; }

        .fp-upload { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 16px; border: 2px dashed #d1d5db; border-radius: 12px; cursor: pointer; font-size: 12px; color: #6b7280; }
        .fp-upload:hover { border-color: rgb(99 102 241); color: rgb(79 70 229); }
        .fp-upload svg { width: 16px; height: 16px; }

        .fp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(88px, 1fr)); gap: 4px; max-height: 28rem; overflow-y: auto; padding-right: 4px; }
        .fp-item { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 8px 6px; border-radius: 10px; cursor: pointer; user-select: none; background: none; border: none; font: inherit; }
        .fp-item:hover { background: rgb(238 242 255); }
        .fp-item.fp-selected { background: rgb(224 231 255); box-shadow: inset 0 0 0 1px rgb(165 180 252); }
        .fp-thumb { width: 56px; height: 56px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; overflow: hidden; box-shadow: inset 0 0 0 1px #e5e7eb; }
        .fp-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .fp-thumb svg { width: 28px; height: 28px; }
        .fp-folder-icon { width: 44px; height: 44px; color: #fbbf24; }
        .fp-name { font-size: 11px; line-height: 1.25; text-align: center; overflow-wrap: anywhere; color: #374151; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        .fp-empty { grid-column: 1 / -1; text-align: center; color: #9ca3af; font-size: 12px; padding: 40px 0; }
        .fp-empty svg { width: 32px; height: 32px; margin: 0 auto 8px; color: #d1d5db; }
    </style>
    <div class="fp-crumbs">
        <button type="button" wire:click="goTo('')">
            <x-heroicon-o-home />
            Inicio
        </button>
        @foreach ($this->breadcrumbs() as $crumb)
            <x-heroicon-o-chevron-right />
            <button type="button" wire:click="goTo('{{ $crumb['path'] }}')">{{ $crumb['label'] }}</button>
        @endforeach
    </div>

    <div>
        <label class="fp-upload">
            <x-heroicon-o-arrow-up-tray />
            <span wire:loading.remove wire:target="nuevoArchivo">Subir un archivo nuevo a esta carpeta&hellip;</span>
            <span wire:loading wire:target="nuevoArchivo">Subiendo&hellip;</span>
            <input type="file" wire:model="nuevoArchivo" class="hidden" />
        </label>
        @error('nuevoArchivo')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Grilla al estilo explorador de Windows: carpetas y archivos uno al lado del otro,
         varios por fila. Un click selecciona/resalta; en una carpeta hace falta doble click
         para entrar (un solo click ya te sacaba de la carpeta antes, fácil de disparar sin
         querer). Los archivos se siguen eligiendo con un solo click -- este modal no tiene un
         paso de "confirmar selección", así que pedir doble click ahí sería redundante. --}}
    <div class="fp-grid">
        @foreach ($this->folders() as $folder)
            <div
                @click="selected = 'folder-{{ $loop->index }}'"
                wire:dblclick="goTo('{{ trim($path . '/' . $folder, '/') }}')"
                :class="selected === 'folder-{{ $loop->index }}' ? 'fp-selected' : ''"
                class="fp-item"
            >
                <div class="fp-thumb">
                    <x-heroicon-s-folder class="fp-folder-icon" />
                </div>
                <span class="fp-name">{{ $folder }}</span>
            </div>
        @endforeach

        @foreach ($this->files() as $file)
            <button
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('archivo-seleccionado', { detail: { url: '{{ $file['url'] }}', path: '{{ $file['path'] }}' } }))"
                @click="selected = 'file-{{ $loop->index }}'"
                :class="selected === 'file-{{ $loop->index }}' ? 'fp-selected' : ''"
                class="fp-item"
            >
                <div class="fp-thumb">
                    @if (in_array($file['ext'], ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <img src="{{ $file['url'] }}" loading="lazy" />
                    @elseif ($file['ext'] === 'pdf')
                        <x-heroicon-o-document-text style="color:#f87171" />
                    @elseif ($file['ext'] === 'zip')
                        <x-heroicon-o-archive-box style="color:#f59e0b" />
                    @else
                        <x-heroicon-o-document style="color:#9ca3af" />
                    @endif
                </div>
                <span class="fp-name">{{ $file['name'] }}</span>
            </button>
        @endforeach

        @if ($this->folders()->isEmpty() && $this->files()->isEmpty())
            <div class="fp-empty">
                <x-heroicon-o-folder-open />
                <div>Carpeta vacía.</div>
            </div>
        @endif
    </div>
</div>
