<div class="space-y-4" wire:loading.class="opacity-60">
    <div class="flex items-center flex-wrap gap-1.5 text-xs">
        <button type="button" wire:click="goTo('')" class="font-medium text-primary-600 hover:underline">
            Inicio
        </button>
        @foreach ($this->breadcrumbs() as $crumb)
            <span class="text-gray-400">/</span>
            <button type="button" wire:click="goTo('{{ $crumb['path'] }}')" class="font-medium text-primary-600 hover:underline">
                {{ $crumb['label'] }}
            </button>
        @endforeach
    </div>

    <div>
        <label class="flex items-center justify-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 dark:border-white/10 rounded-xl cursor-pointer text-xs text-gray-500 dark:text-gray-400 hover:border-primary-500 transition-colors">
            <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
            <span wire:loading.remove wire:target="nuevoArchivo">Subir un archivo nuevo a esta carpeta&hellip;</span>
            <span wire:loading wire:target="nuevoArchivo">Subiendo&hellip;</span>
            <input type="file" wire:model="nuevoArchivo" class="hidden" />
        </label>
        @error('nuevoArchivo')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 max-h-96 overflow-y-auto pr-1">
        @foreach ($this->folders() as $folder)
            <button
                type="button"
                wire:click="goTo('{{ trim($path . '/' . $folder, '/') }}')"
                class="p-3 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 flex flex-col items-center gap-1.5 hover:border-primary-500 transition-colors"
            >
                <x-heroicon-o-folder class="w-8 h-8 text-amber-400 shrink-0" />
                <span class="text-[11px] text-center break-all line-clamp-1">{{ $folder }}</span>
            </button>
        @endforeach

        @foreach ($this->files() as $file)
            <button
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('archivo-seleccionado', { detail: { url: '{{ $file['url'] }}' } }))"
                class="p-3 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 flex flex-col items-center gap-1.5 hover:border-primary-500 transition-colors"
            >
                @if (in_array($file['ext'], ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    <img src="{{ $file['url'] }}" class="w-8 h-8 object-cover rounded" loading="lazy" />
                @else
                    <x-heroicon-o-document class="w-8 h-8 text-gray-400 shrink-0" />
                @endif
                <span class="text-[11px] text-center break-all line-clamp-1">{{ $file['name'] }}</span>
            </button>
        @endforeach

        @if ($this->folders()->isEmpty() && $this->files()->isEmpty())
            <div class="col-span-full text-center text-gray-400 text-xs py-8">
                Carpeta vacía.
            </div>
        @endif
    </div>
</div>
