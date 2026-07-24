@php
    $textarea = $textarea ?? false;
    $placeholder = $placeholder ?? '';
    $rows = $rows ?? 3;
    $model = 'fields.' . $name;
@endphp
<div class="space-y-1">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @if($textarea)
        <textarea wire:model="{{ $model }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
            class="block w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
    @else
        <input type="text" wire:model="{{ $model }}" placeholder="{{ $placeholder }}"
            class="block w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
    @endif
</div>
