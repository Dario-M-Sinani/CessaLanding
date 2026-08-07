<div
    x-data
    x-on:archivo-seleccionado.window="
        @if(($valueType ?? 'url') === 'path')
            @if($multiple ?? false)
            $wire.set('data.{{ $target }}', [...Object.values($wire.get('data.{{ $target }}') || {}), $event.detail.path]);
            @else
            $wire.set('data.{{ $target }}', [$event.detail.path]);
            @endif
        @else
        $wire.set('data.{{ $target }}', $event.detail.{{ $valueType ?? 'url' }});
        @endif
        $wire.unmountFormComponentAction(false, false);
    "
>
    <livewire:file-picker-browser :path="$directory" :key="'picker-' . $target" />
</div>
