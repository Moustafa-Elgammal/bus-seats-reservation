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
                    Trips
                    <div>
                        <form action="{{route('trip.create')}}" method="post">
                            <input name="name" placeholder="Trip name" required autofocus>
                            <select name="bus_id" required>
                                @foreach($buses as $bus)
                                    <option value="{{$bus->id}}">{{$bus->name}}</option>
                                @endforeach
                            </select>
                            <input type="submit" value="save" class="bg-gray-200 p-2 rounded">
                            @csrf
                        </form>
                    </div>
                </div>

                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach($trips->reverse() as $trip)
                        <p class="p-6">
                                {{$trip->name}}
                    <?php
                        $exi_cities = [];
                        ?>
                                @foreach($trip->stations as $station)
                                    <div class="p-2 ml-8 bg-fuchsia-100">
                                        {{$station->city->name ?? 'deleted'}}

                                        <?php $exi_cities[] = $station->city_id?>
                                    </div>
                                @endforeach

                            <div class="p-2 ml-8 bg-fuchsia-100">
                            <form action="{{url('trip/station/'.$trip->id)}}" method="post">
                                <select name="city_id" required>
                                    @foreach($cities as $city)
                                        @continue(in_array($city->id, $exi_cities))
                                        <option value="{{$city->id}}">{{$city->name}}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="last_order" value="{{$station->station_order ?? 0}}">
                                <input type="submit" value="save" class="bg-gray-200 p-2 rounded">
                                @csrf
                            </form>
                        </p>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
