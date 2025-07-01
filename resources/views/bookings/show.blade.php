<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Booking Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Messaggio di successo -->
                    <x-auth-session-status class="mb-4" :status="session('success')" />

                    {{-- Nuovo layout a griglia per i dettagli --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Field</h3>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $booking->field->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Booked by</h3>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $booking->user->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</h3>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $booking->start_time->format('d F Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Time</h3>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100 capitalize">{{ $booking->status }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Price</h3>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">€{{ number_format($booking->total_price, 2) }}</p>
                        </div>
                    </div>

                    {{-- Pulsanti di azione --}}
                    <div class="mt-8 flex items-center space-x-4">
                        {{-- NUOVO: Pulsante per tornare indietro --}}
                        <a href="{{ route('bookings.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Back to List') }}
                        </a>

                        @can('update', $booking)
                            <a href="{{ route('bookings.edit', $booking->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Edit') }}
                            </a>
                        @endcan

                        @can('delete', $booking)
                            <form method="POST" action="{{ route('bookings.destroy', $booking->id) }}" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                @csrf
                                @method('DELETE')
                                <x-danger-button type="submit">
                                    {{ __('Cancel Booking') }}
                                </x-danger-button>
                            </form>
                        @endcan
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
