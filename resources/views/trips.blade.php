<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trips') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('partials.admin-feedback')

                    Trips
                    <div>
                        <form action="{{ route('trip.create') }}" method="post">
                            <input name="name" placeholder="Trip name" required autofocus>
                            <select name="bus_id" required>
                                @foreach($buses as $bus)
                                    <option value="{{ $bus->id }}">{{ $bus->name }}</option>
                                @endforeach
                            </select>
                            <input type="submit" value="save" class="bg-gray-200 p-2 rounded">
                            @csrf
                        </form>
                    </div>
                </div>

                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach($trips->reverse() as $trip)
                        <div class="p-6">
                            <div class="flex items-center gap-2">
                                <form action="{{ route('trip.update', $trip) }}" method="post" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input name="name" value="{{ $trip->name }}" required>
                                    <input type="submit" value="rename" class="bg-gray-200 p-2 rounded">
                                </form>

                                <form action="{{ route('trip.destroy', $trip) }}" method="post"
                                      onsubmit="return confirm('{{ __('Delete :trip?', ['trip' => $trip->name]) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="submit" value="delete" class="bg-red-200 p-2 rounded">
                                </form>
                            </div>

                            @php $routeCityIds = $trip->stations->pluck('city_id')->all(); @endphp

                            @foreach($trip->stations as $station)
                                <div class="p-2 ml-8 bg-fuchsia-100 flex items-center gap-2">
                                    <span>{{ $station->city->name ?? __('deleted') }}</span>

                                    <form action="{{ route('trip.station.destroy', $station) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <input type="submit" value="remove" class="bg-red-200 px-2 rounded">
                                    </form>
                                </div>
                            @endforeach

                            <div class="p-2 ml-8 bg-fuchsia-100">
                                <form action="{{ route('trip.station.add', $trip->id) }}" method="post">
                                    <select name="city_id" required>
                                        @foreach($cities as $city)
                                            @continue(in_array($city->id, $routeCityIds))
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="submit" value="save" class="bg-gray-200 p-2 rounded">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
