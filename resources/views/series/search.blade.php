@extends('layouts.app')

@section('content')

    <div class="mb-10">

        <h1 class="text-4xl font-bold tracking-tight">
            Rechercher une série
        </h1>

        <p class="mt-2 text-gray-500">
            Recherche une série à ajouter à ton tracker.
        </p>

    </div>


    {{-- Formulaire de recherche --}}
    <form action="{{ route('series.search') }}" method="GET" class="mb-10">

        <div class="flex max-w-2xl gap-3">

            <input type="text" name="query" value="{{ request('query') }}" placeholder="Ex : Breaking Bad"
                class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-200">

            <button type="submit"
                class="rounded-xl bg-gray-900 px-6 py-3 font-medium text-white transition hover:bg-gray-700">
                Rechercher
            </button>

        </div>

    </form>


    {{-- Résultats --}}
    @isset($results)
        @if (count($results) === 0)
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm">

                <p class="text-gray-500">
                    Aucune série trouvée.
                </p>

            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">

                @foreach ($results as $result)
                    @php
                        $show = $result['show'];
                    @endphp

                    <article
                        class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">

                        {{-- Affiche --}}
                        @if (!empty($show['image']['original']))
                            <img src="{{ $show['image']['original'] }}" alt="{{ $show['name'] }}"
                                class="h-64 w-full object-cover">
                        @else
                            <div class="flex h-64 items-center justify-center bg-gray-200 text-sm text-gray-400">
                                Pas d'affiche
                            </div>
                        @endif


                        <div class="flex flex-1 flex-col p-5">

                            <h2 class="text-xl font-bold">
                                {{ $show['name'] }}
                            </h2>


                            @if (!empty($show['premiered']))
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($show['premiered'])->format('Y') }}
                                </p>
                            @endif


                            {{-- Ajouter --}}
                            @if ($result['already_added'])
                                <div class="mt-auto pt-5">

                                    <button type="button" disabled
                                        class="w-full cursor-not-allowed rounded-xl bg-gray-100 px-4 py-2.5 font-medium text-gray-500">
                                        ✓ Déjà dans mes séries
                                    </button>

                                </div>
                            @else
                                <form action="{{ route('series.store') }}" method="POST" class="mt-auto pt-5">

                                    @csrf

                                    <input type="hidden" name="tvmaze_id" value="{{ $show['id'] }}">

                                    <input type="hidden" name="name" value="{{ $show['name'] }}">

                                    <input type="hidden" name="image_url" value="{{ $show['image']['original'] ?? '' }}">

                                    <button type="submit"
                                        class="w-full rounded-xl bg-gray-900 px-4 py-2.5 font-medium text-white transition hover:bg-gray-700">
                                        + Ajouter à mes séries
                                    </button>

                                </form>
                            @endif

                        </div>

                    </article>
                @endforeach

            </div>
        @endif
    @endisset

@endsection
