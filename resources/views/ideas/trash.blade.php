<x-layout title="Trash">

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            Trash
        </h1>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Ideas you've deleted. You can restore them or permanently delete them.
        </p>
    </div>

    @if ($ideas->isEmpty())
        <div class="card-surface p-8 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Your trash is empty.
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($ideas as $idea)
                <div class="card-surface flex items-center justify-between gap-4 p-5">

                    <div class="min-w-0">
                        <h2 class="font-semibold text-slate-900 dark:text-white">
                            {{ $idea->title }}
                        </h2>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Deleted {{ $idea->deleted_at->diffForHumans() }}
                        </p>
                    </div>

                    <div class="flex shrink-0 gap-2">

                        {{-- Restore --}}
                        <form method="POST" action="{{ route('ideas.restore', $idea) }}">
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                            >
                                Restore
                            </button>
                        </form>

                        {{-- Permanent Delete --}}
                        <form method="POST" action="{{ route('ideas.force-delete', $idea) }}">
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700"
                            >
                                Delete permanently
                            </button>
                        </form>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $ideas->links() }}
        </div>
    @endif

</x-layout>