<x-filament-panels::page>
    <div class="flex items-center flex-wrap gap-1.5 text-sm">
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

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach ($this->folders() as $folder)
            <div
                wire:key="folder-{{ $folder }}"
                class="relative group p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 flex flex-col items-center gap-2 cursor-pointer hover:border-primary-500 transition-colors"
                wire:click="goTo('{{ trim($path . '/' . $folder, '/') }}')"
            >
                <x-heroicon-o-folder class="w-10 h-10 text-amber-400 shrink-0" />
                <span class="text-xs text-center break-all line-clamp-2">{{ $folder }}</span>
                <button
                    type="button"
                    wire:click.stop="askDeleteFolder('{{ trim($path . '/' . $folder, '/') }}', '{{ $folder }}')"
                    class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-600 transition-opacity"
                    title="Eliminar carpeta"
                >
                    <x-heroicon-o-trash class="w-4 h-4" />
                </button>
            </div>
        @endforeach

        @foreach ($this->files() as $file)
            <div wire:key="file-{{ $file['path'] }}" class="relative group p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 flex flex-col items-center gap-2">
                @if (in_array($file['ext'], ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    <img src="{{ $file['url'] }}" class="w-14 h-14 object-cover rounded-lg" loading="lazy" />
                @else
                    <x-heroicon-o-document class="w-10 h-10 text-gray-400 shrink-0" />
                @endif

                <span class="text-xs text-center break-all line-clamp-2" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                <span class="text-[10px] text-gray-400">{{ number_format($file['size'] / 1024, 0) }} KB</span>

                <div class="flex items-center gap-2.5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                        type="button"
                        onclick="navigator.clipboard.writeText('{{ $file['url'] }}')"
                        class="text-gray-400 hover:text-primary-600"
                        title="Copiar enlace"
                    >
                        <x-heroicon-o-link class="w-4 h-4" />
                    </button>
                    <a href="{{ $file['url'] }}" target="_blank" class="text-gray-400 hover:text-primary-600" title="Ver archivo">
                        <x-heroicon-o-eye class="w-4 h-4" />
                    </a>
                    <button
                        type="button"
                        wire:click="askDeleteFile('{{ $file['path'] }}', '{{ $file['name'] }}')"
                        class="text-gray-400 hover:text-red-600"
                        title="Eliminar archivo"
                    >
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endforeach

        @if ($this->folders()->isEmpty() && $this->files()->isEmpty())
            <div class="col-span-full text-center text-gray-400 text-sm py-16">
                Esta carpeta está vacía.
            </div>
        @endif
    </div>

    <x-filament::modal id="confirmar-eliminar" width="sm">
        <x-slot name="heading">
            {{ $confirmDeleteType === 'folder' ? 'Eliminar Carpeta' : 'Eliminar Archivo' }}
        </x-slot>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            ¿Está seguro de eliminar
            @if ($confirmDeleteType === 'folder')
                la carpeta <strong>{{ $confirmDeleteLabel }}</strong> y todo su contenido?
            @else
                el archivo <strong>{{ $confirmDeleteLabel }}</strong>?
            @endif
            Esta acción no se puede deshacer.
        </p>

        <x-slot name="footerActions">
            <x-filament::button color="gray" x-on:click="close">
                Cancelar
            </x-filament::button>
            <x-filament::button color="danger" wire:click="confirmDelete">
                Eliminar
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
