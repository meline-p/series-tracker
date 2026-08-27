<nav class="border-b bg-white">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">

        <a href="{{ route('series.index') }}" class="text-xl font-bold">
            Series Tracker
        </a>

        <div class="flex items-center gap-6 text-sm font-medium">

            <a href="{{ route('series.index') }}" class="text-gray-600 transition hover:text-gray-900">
                Mes séries
            </a>

            <a href="{{ route('series.search') }}" class="text-gray-600 transition hover:text-gray-900">
                Rechercher
            </a>

        </div>

    </div>
</nav>
