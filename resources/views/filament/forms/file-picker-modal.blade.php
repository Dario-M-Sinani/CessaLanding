{{-- Un solo request atómico: callMountedFormComponentAction() corre el ->action() de
     FileManagerAction (que escribe el valor elegido) y cierra el modal en el mismo commit --
     ver el comentario en app/Filament/Support/FileManagerAction.php sobre por qué NO conviene
     hacer esto en dos pasos separados desde aquí ($wire.set() + cerrar el modal a mano). --}}
<div
    x-data
    x-on:archivo-seleccionado.window="$wire.callMountedFormComponentAction({ path: $event.detail.path, url: $event.detail.url })"
>
    <livewire:file-picker-browser :path="$directory" :key="'picker-' . $target" />
</div>
