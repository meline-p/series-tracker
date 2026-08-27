@extends('layouts.app')

@section('content')
    {{-- En-tête de la série --}}
    <div class="mb-10">

        <a href="{{ route('series.index') }}"
            class="mb-6 inline-flex items-center text-sm font-medium text-gray-500 transition hover:text-gray-900">
            ← Retour à mes séries
        </a>

        <div class="flex flex-col gap-6 sm:flex-row">

            @if ($series->image_url)
                <img src="{{ $series->image_url }}" alt="{{ $series->name }}"
                    class="h-72 w-48 rounded-2xl object-cover shadow-md">
            @endif

            <div class="flex flex-col justify-center">

                <h1 class="text-4xl font-bold tracking-tight">
                    {{ $series->name }}
                </h1>

                <p class="mt-3 text-gray-500">
                    {{ $series->seasons->count() }}
                    {{ $series->seasons->count() > 1 ? 'saisons' : 'saison' }}
                </p>

            </div>

        </div>

    </div>


    {{-- Saisons --}}
    <div class="space-y-4">

        @foreach ($series->seasons->sortBy('season_number') as $season)
            @php
                $totalEpisodes = $season->episodes->count();
                $watchedEpisodes = $season->episodes->filter(fn($episode) => $episode->watchedEpisode)->count();

                $seasonCompleted = $totalEpisodes > 0 && $watchedEpisodes === $totalEpisodes;
            @endphp

            <details class="group overflow-hidden rounded-2xl bg-white shadow-sm">

                {{-- Titre de la saison --}}
                <summary
                    class="flex cursor-pointer list-none items-center justify-between px-6 py-5 transition hover:bg-gray-50">

                    <div class="flex items-center gap-3">

                        <h2 class="text-xl font-bold">
                            Saison {{ $season->season_number }}
                        </h2>

                        @if ($watchedEpisodes > 0 && !$seasonCompleted)
                            <span class="text-sm font-semibold text-gray-500">
                                {{ $watchedEpisodes }}/{{ $totalEpisodes }} épisodes
                            </span>
                        @else
                            <span class="text-sm text-gray-500">
                                {{ $totalEpisodes }}
                                {{ $totalEpisodes > 1 ? 'épisodes' : 'épisode' }}
                            </span>
                        @endif

                        @if ($seasonCompleted)
                            <span class="text-lg text-green-500" title="Saison terminée">
                                ✓
                            </span>
                        @endif

                    </div>


                    {{-- Flèche --}}
                    <span class="text-xl text-gray-400 transition-transform duration-200 group-open:rotate-180">
                        ↓
                    </span>

                </summary>


                {{-- Episodes --}}
                <div class="divide-y border-t">

                    @foreach ($season->sortedEpisodes() as $episode)
                        <div class="flex items-center gap-4 px-6 py-4 transition hover:bg-gray-50">

                            {{-- Bouton vu --}}
                            <form action="{{ route('episodes.toggle-watched', $episode) }}" method="POST">

                                @csrf

                                <button type="submit"
                                    class="flex h-8 w-8 items-center justify-center rounded-full border text-lg transition
                                {{ $episode->watchedEpisode
                                    ? 'border-green-500 bg-green-500 text-white'
                                    : 'border-gray-300 text-transparent hover:border-gray-500' }}"
                                    title="{{ $episode->watchedEpisode ? 'Marqué comme vu' : 'Marquer comme vu' }}">
                                    ✓
                                </button>

                            </form>


                            {{-- Numéro --}}
                            <div class="w-16 shrink-0 text-sm font-semibold text-gray-500">

                                @if ($episode->episode_number)
                                    E{{ str_pad($episode->episode_number, 2, '0', STR_PAD_LEFT) }}
                                @else
                                    <span class="text-xs">
                                        Spécial
                                    </span>
                                @endif

                            </div>


                            {{-- Nom --}}
                            <div class="min-w-0 flex-1">

                                <p
                                    class="font-medium
                                {{ $episode->watchedEpisode ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                                    {{ $episode->name }}
                                </p>

                                @if ($episode->air_date)
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ $episode->air_date->format('d/m/Y') }}
                                    </p>
                                @endif

                            </div>

                        </div>
                    @endforeach

                </div>

            </details>
        @endforeach

    </div>
@endsection
