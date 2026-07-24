{{-- Generic in-page hero (shown after entering). --}}
@php
    $kind = $theme['kind'] ?? 'general';
    $isWedding = $kind === 'wedding';
    $title = $landing['title'] ?: __('undangan.event_title');
    $groom = $landing['groom_name'] ?? '';
    $bride = $landing['bride_name'] ?? '';
@endphp

<div class="text-center space-y-3 pt-4">
    @if($heroPhoto)
        <img src="{{ $heroPhoto }}" class="mx-auto h-40 w-40 rounded-full object-cover shadow-lg ring-4 ring-white" alt="{{ $title }}">
    @endif

    @switch($template)
        @case('wedding-islam')
            <p class="text-xs uppercase tracking-[0.3em] text-gray-500">Walimatul 'Urs</p>
            @break
        @case('tech')
            <p class="font-mono text-sm text-accent">&lt;/&gt; {{ $landing['host_org'] ?: 'meetup' }}</p>
            @break
        @case('corporate')
            @if($landing['host_org'])<p class="text-xs uppercase tracking-[0.3em] text-accent font-semibold">{{ $landing['host_org'] }}</p>@endif
            @break
    @endswitch

    @if($isWedding && ($groom || $bride))
        <h1 class="font-display text-2xl md:text-3xl font-bold text-gray-800">{{ $groom }} &amp; {{ $bride }}</h1>
    @else
        <h1 class="font-display text-2xl md:text-3xl font-bold text-gray-800">{{ $title }}</h1>
    @endif

    @if($landing['subtitle'])
        <p class="font-display text-base text-accent italic">{{ $landing['subtitle'] }}</p>
    @endif
    @if($landing['event_date_text'])
        <p class="text-sm text-gray-500">{{ $landing['event_date_text'] }}{{ $landing['event_time_text'] ? ' · '.$landing['event_time_text'] : '' }}</p>
    @endif

    {{-- Wedding: full names & parents --}}
    @if($isWedding && ($landing['groom_full'] || $landing['bride_full']))
        <div class="glass-card rounded-2xl p-5 shadow-lg mt-2 space-y-4">
            @if($landing['groom_full'])
                <div>
                    <p class="font-display text-lg font-bold text-gray-800">{{ $landing['groom_full'] }}</p>
                    @if($landing['groom_parents'])<p class="text-xs text-gray-500 whitespace-pre-line">{{ $landing['groom_parents'] }}</p>@endif
                </div>
            @endif
            @if($landing['groom_full'] && $landing['bride_full'])<div class="u-divider"></div>@endif
            @if($landing['bride_full'])
                <div>
                    <p class="font-display text-lg font-bold text-gray-800">{{ $landing['bride_full'] }}</p>
                    @if($landing['bride_parents'])<p class="text-xs text-gray-500 whitespace-pre-line">{{ $landing['bride_parents'] }}</p>@endif
                </div>
            @endif
        </div>
    @endif
</div>
