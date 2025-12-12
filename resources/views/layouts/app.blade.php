<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Data Nexus Dashboard' }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    {{-- Style CSS --}}
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    @stack('styles')
</head>

<body>

    <div class="dashboard-container">

        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Main Content --}}
        <main class="main-content">
            @yield('content')
        </main>

    </div>

    {{-- Footer --}}
    @include('partials.footer')
    @stack('scripts')
    <script src="{{ asset('scripts.js') }}"></script>
</body>

</html>
