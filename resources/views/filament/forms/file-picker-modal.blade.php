<div
    x-data
    x-on:archivo-seleccionado.window="
        $wire.set('data.{{ $target }}', $event.detail.url);
        $wire.unmountFormComponentAction(false, false);
    "
>
    <livewire:file-picker-browser :path="$directory" :key="'picker-' . $target" />
</div>
