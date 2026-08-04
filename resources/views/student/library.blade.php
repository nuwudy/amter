<x-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-10">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">My Course Library</h1>
            <p class="mt-2 text-slate-600">Explore, learn, and master new skills.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-8">
            @forelse($sessions as $session)
                <x-library-card :record="$session" />
            @empty
                <div class="col-span-full text-center py-20 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                    <h3 class="text-lg font-medium text-slate-900">No titles found</h3>
                    <p class="text-slate-500 mt-1">Check back later for new content.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
