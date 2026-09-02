@if (session('success'))
    <div class="p-2 mb-2 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
@endif

@foreach (session('service_errors', []) as $serviceError)
    <div class="p-2 mb-2 rounded bg-red-100 text-red-800">{{ $serviceError }}</div>
@endforeach

@foreach ($errors->all() as $error)
    <div class="p-2 mb-2 rounded bg-red-100 text-red-800">{{ $error }}</div>
@endforeach
