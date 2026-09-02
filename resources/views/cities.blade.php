<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cities') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('partials.admin-feedback')

                    Cities
                    <div>
                        <form action="{{ route('city.create') }}" method="post">
                            <input name="name" placeholder="city name" required autofocus>
                            <input type="submit" value="save" class="bg-gray-200 p-2 rounded">
                            @csrf
                        </form>
                    </div>
                </div>

                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach($cities->reverse() as $city)
                        <div class="p-6 flex items-center gap-2">
                            <form action="{{ route('city.update', $city) }}" method="post" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <input name="name" value="{{ $city->name }}" required>
                                <input type="submit" value="rename" class="bg-gray-200 p-2 rounded">
                            </form>

                            <form action="{{ route('city.destroy', $city) }}" method="post"
                                  onsubmit="return confirm('{{ __('Delete :city?', ['city' => $city->name]) }}')">
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
