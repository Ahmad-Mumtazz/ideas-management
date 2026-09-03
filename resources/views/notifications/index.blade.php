<x-layout title="Notifications">

    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                Notifications
            </h1>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Stay up to date with your ideas.
            </p>
        </div>

        @if ($notifications->contains(fn ($notification) => $notification->read_at === null))
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                >
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <div class="card-surface p-8 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                You have no notifications.
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                <div
                    @class([
                        'card-surface flex items-start justify-between gap-4 p-5',
                        'ring-2 ring-indigo-500/20' => $notification->read_at === null,
                    ])
                >
                    <div class="min-w-0">
                        <h2 class="font-semibold text-slate-900 dark:text-white">
                            {{ $notification->data['title'] ?? 'Notification' }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                            {{ $notification->data['message'] ?? '' }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>

                    @if ($notification->read_at === null)
                        <form
                            method="POST"
                            action="{{ route('notifications.read', $notification) }}"
                            class="shrink-0"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                Mark as read
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif

</x-layout>
