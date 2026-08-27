@extends('layouts.app')

@section('content')

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-4xl font-bold tracking-tight">
                Mes séries
            </h1>

            <p class="mt-2 text-gray-500">
                Suis facilement les séries que tu regardes.
            </p>
        </div>

        <form action="{{ route('series.sync') }}" method="POST">
            @csrf

            <button type="submit"
                class="rounded-lg bg-gray-900 px-5 py-3 font-medium text-white transition hover:bg-gray-700">
                🔄 Mettre à jour
            </button>
        </form>

    </div>


    {{-- Message de synchronisation --}}
    @if (session()->has('updates'))

        <div class="mt-8 rounded-xl bg-white p-6 shadow-sm">

            <h2 class="mb-4 text-xl font-semibold">
                🔄 Mise à jour terminée
            </h2>

            @if (count(session('updates')) === 0)
                <p class="text-gray-500">
                    Aucune nouveauté.
                </p>
            @else
                <div class="space-y-4">

                    @foreach (session('updates') as $update)
                        <div>

                            <h3 class="font-semibold">
                                {{ $update['series']->name }}
                            </h3>

                            @foreach ($update['new_seasons'] as $season)
                                <p class="text-gray-600">
                                    🆕 Saison {{ $season->season_number }}
                                    — {{ $season->episodes()->count() }} épisodes
                                </p>
                            @endforeach

                            @foreach ($update['new_episodes'] as $episode)
                                <p class="text-gray-600">
                                    🆕

                                    @if ($episode->episode_number)
                                        S{{ str_pad($episode->season->season_number, 2, '0', STR_PAD_LEFT) }}
                                        E{{ str_pad($episode->episode_number, 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        Spécial
                                    @endif

                                    — {{ $episode->name }}

                                </p>
                            @endforeach

                        </div>
                    @endforeach

                </div>
            @endif

        </div>

    @endif


    {{-- Mes séries --}}
    <section class="mt-10">

        <h2 class="mb-6 text-2xl font-bold">
            Mes séries
        </h2>

        @if ($series->isEmpty())
            <div class="rounded-xl bg-white p-10 text-center shadow-sm">

                <p class="text-gray-500">
                    Tu ne suis encore aucune série.
                </p>

            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">

                @foreach ($series as $mySeries)
                    <article
                        class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">

                        @if ($mySeries->image_url)
                            <img src="{{ $mySeries->image_url }}" alt="{{ $mySeries->name }}"
                                class="h-80 w-full object-cover">
                        @endif


                        <div class="flex flex-1 flex-col p-5">

                            <h3 class="text-xl font-bold">
                                {{ $mySeries->name }}
                            </h3>


                            @if ($mySeries->nextEpisode)
                                <p class="mt-3 text-sm text-gray-500">
                                    À regarder
                                </p>

                                <p class="font-semibold">

                                    S{{ str_pad($mySeries->nextEpisode->season->season_number, 2, '0', STR_PAD_LEFT) }}

                                    E{{ str_pad($mySeries->nextEpisode->episode_number, 2, '0', STR_PAD_LEFT) }}

                                    <span class="font-normal text-gray-600">
                                        — {{ $mySeries->nextEpisode->name }}
                                    </span>

                                </p>
                            @elseif ($mySeries->seasons->contains(fn($season) => $season->episodes->isEmpty()))
                                <p class="mt-3 font-medium text-blue-600">
                                    ✓ À jour
                                </p>
                            @else
                                <p class="mt-3 font-medium text-green-600">
                                    ✓ Série terminée
                                </p>
                            @endif


                            <a href="{{ route('series.show', $mySeries) }}" class="mt-auto pt-5">
                                <span
                                    class="inline-block rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">
                                    Voir la série
                                </span>
                            </a>

                        </div>

                    </article>
                @endforeach

            </div>
        @endif

    </section>

@endsection
