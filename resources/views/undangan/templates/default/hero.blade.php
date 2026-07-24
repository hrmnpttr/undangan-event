{{-- Default hero: original centered title + tagline. --}}
<div class="text-center space-y-2 pt-4">
    <h1 class="font-display text-2xl md:text-3xl font-bold text-gray-800">
        {{ $landing['title'] ?: __('undangan.event_title') }}
    </h1>
    <p class="font-display text-base text-accent italic">
        {{ $landing['subtitle'] ?: __('undangan.tagline') }}
    </p>
    @if($landing['event_date_text'])
        <p class="text-sm text-gray-500">{{ $landing['event_date_text'] }}{{ $landing['event_time_text'] ? ' · '.$landing['event_time_text'] : '' }}</p>
    @endif
</div>
