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

            <form action="{{ route('database.export') }}" method="POST">
                @csrf

                <button type="submit"
                    class="text-sm font-medium text-gray-600 transition hover:text-gray-900 cursor-pointer">
                    💾 Exporter
                </button>
            </form>

            <form action="{{ route('database.import') }}" method="POST"
                onsubmit="return confirm('⚠️ La base de données actuelle sera remplacée par la sauvegarde. Continuer ?');">

                @csrf

                <button type="submit"
                    class="text-sm font-medium text-gray-600 transition hover:text-gray-900 cursor-pointer">
                    ↩ Importer
                </button>
            </form>

        </div>

    </div>
</nav>
