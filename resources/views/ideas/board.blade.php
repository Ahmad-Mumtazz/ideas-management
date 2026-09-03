<x-layout title="Idea Board" wide>

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                Idea Board
            </h1>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Organize your ideas by their current status.
            </p>
        </div>

        <a
            href="{{ route('ideas.create') }}"
            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
        >
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>

            New idea
        </a>
    </div>

    {{-- Kanban columns --}}
    <div class="grid items-start gap-5 lg:grid-cols-3">

        {{-- Pending --}}
        <section
    class="idea-board-column min-w-0"
    data-status="pending"
>
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full bg-slate-400"></span>

                    <h2 class="font-semibold text-slate-900 dark:text-white">
                        Pending
                    </h2>
                </div>

                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold tabular-nums text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    {{ $ideas->get('pending', collect())->count() }}
                </span>
            </div>

            <div
    class="idea-board-dropzone min-h-32 space-y-3 rounded-xl bg-slate-50 p-3 transition dark:bg-slate-900/50"
    data-status="pending"
>
                @forelse ($ideas->get('pending', collect()) as $idea)
                    <x-idea-board-card :idea="$idea" />
                @empty
                    <div class="flex min-h-24 items-center justify-center rounded-lg border border-dashed border-slate-300 dark:border-slate-700">
                        <p class="text-sm text-slate-400 dark:text-slate-500">
                            No pending ideas
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- In Progress --}}
        <section
    class="idea-board-column min-w-0"
    data-status="pending"
>
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full bg-sky-500"></span>

                    <h2 class="font-semibold text-slate-900 dark:text-white">
                        In Progress
                    </h2>
                </div>

                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold tabular-nums text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">
                    {{ $ideas->get('in_progress', collect())->count() }}
                </span>
            </div>

            <div
    class="idea-board-dropzone min-h-32 space-y-3 rounded-xl bg-slate-50 p-3 transition dark:bg-slate-900/50"
    data-status="pending"
>
                @forelse ($ideas->get('in_progress', collect()) as $idea)
                    <x-idea-board-card :idea="$idea" />
                @empty
                    <div class="flex min-h-24 items-center justify-center rounded-lg border border-dashed border-slate-300 dark:border-slate-700">
                        <p class="text-sm text-slate-400 dark:text-slate-500">
                            No ideas in progress
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Completed --}}
        <section
    class="idea-board-column min-w-0"
    data-status="pending"
>
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full bg-emerald-500"></span>

                    <h2 class="font-semibold text-slate-900 dark:text-white">
                        Completed
                    </h2>
                </div>

                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold tabular-nums text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                    {{ $ideas->get('completed', collect())->count() }}
                </span>
            </div>

            <div
    class="idea-board-dropzone min-h-32 space-y-3 rounded-xl bg-slate-50 p-3 transition dark:bg-slate-900/50"
    data-status="pending"
>
                @forelse ($ideas->get('completed', collect()) as $idea)
                    <x-idea-board-card :idea="$idea" />
                @empty
                    <div class="flex min-h-24 items-center justify-center rounded-lg border border-dashed border-slate-300 dark:border-slate-700">
                        <p class="text-sm text-slate-400 dark:text-slate-500">
                            No completed ideas
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

    </div>

</x-layout>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        let draggedCard = null;

        const cards = document.querySelectorAll('.idea-board-card');
        const dropzones = document.querySelectorAll('.idea-board-dropzone');

        cards.forEach(card => {
            card.addEventListener('dragstart', () => {
                draggedCard = card;

                card.classList.add('opacity-50');
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('opacity-50');

                draggedCard = null;

                dropzones.forEach(dropzone => {
                    dropzone.classList.remove('ring-2', 'ring-indigo-400');
                });
            });
        });

        dropzones.forEach(dropzone => {
            dropzone.addEventListener('dragover', event => {
                event.preventDefault();

                dropzone.classList.add('ring-2', 'ring-indigo-400');
            });

            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('ring-2', 'ring-indigo-400');
            });

            dropzone.addEventListener('drop', async event => {
                event.preventDefault();

                dropzone.classList.remove('ring-2', 'ring-indigo-400');

                if (!draggedCard) {
                    return;
                }

                const ideaId = draggedCard.dataset.ideaId;
                const newStatus = dropzone.dataset.status;

                const response = await fetch(`/ideas/${ideaId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                    },
                    body: JSON.stringify({
                        status: newStatus,
                    }),
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Unable to update the idea status.');
                }
            });
        });
    });
</script>