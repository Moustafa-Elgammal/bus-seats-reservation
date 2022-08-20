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
                    Buses
                    <div>
                        <form action="{{route('bus.create')}}" method="post">
                            <input name="name" placeholder="Bus name" required autofocus>
                            <input name="seats_capacity" value="12" type="number" min="1" placeholder="Bus Seats Capacity" required>
                            <input type="submit" value="save" class="bg-gray-200 p-2 rounded">
                            @csrf
                        </form>
                    </div>
                </div>

                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach($buses->reverse() as $bus)
                        <div class="p-6">
                            <h3>
                                {{$bus->name}} || <span>{{$bus->seats_capacity}}</span>
                            </h3>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
