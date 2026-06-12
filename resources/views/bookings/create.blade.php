@extends('layouts.app')

@section('header_title', 'Create Booking')

@section('content')
<div class="max-w-2xl mx-auto cc-card rounded-lg shadow p-8">
    <h2 class="text-2xl font-bold mb-6">Create New Booking</h2>

    <form method="POST" action="{{ route('bookings.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Client -->
            <div>
                <label class="block text-sm font-semibold mb-2 text-[var(--cc-text)]">Client *</label>
                <select name="client_id" required class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2.5 px-4 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                    <option value="" class="bg-[var(--cc-surface)]">Select Client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" class="bg-[var(--cc-surface)]">{{ $client->company_name }}</option>
                    @endforeach
                </select>
                @error('client_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Vehicle -->
            <div>
                <label class="block text-sm font-semibold mb-2 text-[var(--cc-text)]">Vehicle *</label>
                <select name="vehicle_id" required class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2.5 px-4 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                    <option value="" class="bg-[var(--cc-surface)]">Select Vehicle</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" class="bg-[var(--cc-surface)]">{{ $vehicle->model }} ({{ $vehicle->plate_number }})</option>
                    @endforeach
                </select>
                @error('vehicle_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Driver -->
            <div>
                <label class="block text-sm font-semibold mb-2 text-[var(--cc-text)]">Driver *</label>
                <select name="driver_id" required class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2.5 px-4 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                    <option value="" class="bg-[var(--cc-surface)]">Select Driver</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" class="bg-[var(--cc-surface)]">{{ $driver->name }}</option>
                    @endforeach
                </select>
                @error('driver_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Sales (GM only) -->
            @if(auth()->user()->isGM())
            <div>
                <label class="block text-sm font-semibold mb-2 text-[var(--cc-text)]">Sales *</label>
                <select name="sales_id" required class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2.5 px-4 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                    <option value="" class="bg-[var(--cc-surface)]">Select Sales</option>
                    @foreach($sales as $s)
                        <option value="{{ $s->id }}" class="bg-[var(--cc-surface)]">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Pickup DateTime -->
            <div>
                <label class="block text-sm font-semibold mb-2 text-[var(--cc-text)]">Pickup Date & Time *</label>
                <input type="datetime-local" name="pickup_datetime" required class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2 px-4 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                @error('pickup_datetime') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Dropoff DateTime -->
            <div>
                <label class="block text-sm font-semibold mb-2 text-[var(--cc-text)]">Dropoff Date & Time *</label>
                <input type="datetime-local" name="dropoff_datetime" required class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2 px-4 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                @error('dropoff_datetime') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Destination -->
            <div>
                <label class="block text-sm font-semibold mb-2 text-[var(--cc-text)]">Destination *</label>
                <input type="text" name="destination" required class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2 px-4 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="e.g. Bandung">
                @error('destination') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Price -->
            <div>
                <label class="block text-sm font-semibold mb-2 text-[var(--cc-text)]">Price (Rp) *</label>
                <input type="text" name="price" required class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2 px-4 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all idr-input" placeholder="1000000">
                @error('price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-semibold mb-2 text-[var(--cc-text)]">Notes</label>
            <textarea name="notes" rows="4" class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2 px-4 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="Additional notes..."></textarea>
        </div>

        <!-- Buttons -->
        <div class="flex gap-4 pt-6 border-t border-[var(--cc-border)]/50">
            <button type="submit" class="bg-indigo-600 text-gray-900 px-6 py-2.5 rounded-xl font-semibold hover:bg-indigo-500 transition-all">Create Booking</button>
            <a href="{{ route('bookings.index') }}" class="bg-[var(--cc-bg-muted)] text-[var(--cc-text)] border border-[var(--cc-border)] px-6 py-2.5 rounded-xl font-semibold hover:bg-[var(--cc-surface)] transition-all">Cancel</a>
        </div>
    </form>
</div>

<script>
    // IDR input formatting
    document.querySelectorAll('.idr-input').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            this.value = value ? parseInt(value).toLocaleString('id-ID') : '';
        });
    });
</script>
@endsection
