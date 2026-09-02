<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('partials.admin-feedback')

                    Buses
                    <div>
                        <form action="{{ route('bus.create') }}" method="post">
                            <input name="name" placeholder="Bus name" required autofocus>
                            <input name="seats_capacity" value="12" type="number" min="1" max="120" placeholder="Bus Seats Capacity" required>
                            <input type="submit" value="save" class="bg-gray-200 p-2 rounded">
                            @csrf
                        </form>
                    </div>
                </div>

                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach($buses->reverse() as $bus)
                        <div class="p-6 flex items-center gap-2">
                            <form action="{{ route('bus.update', $bus) }}" method="post" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <input name="name" value="{{ $bus->name }}" required>
                                <input name="seats_capacity" type="number" min="1" max="120" value="{{ $bus->seats_capacity }}" required>
                                <input type="submit" value="save" class="bg-gray-200 p-2 rounded">
                            </form>

                            <form action="{{ route('bus.destroy', $bus) }}" method="post"
                                  onsubmit="return confirm('{{ __('Delete :bus?', ['bus' => $bus->name]) }}')">
                                @csrf
                                @method('DELETE')
                                <input type="submit" value="delete" class="bg-red-200 p-2 rounded">
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
