<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/jquery-1.9.1.min.js"></script>
    <script src="/js/vue.js"></script>
    <link rel="stylesheet" href="/css/font-awesome.min.css">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="/css/app.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">

    <!-- Scripts -->

    <script>
        window.Laravel = <?php echo json_encode(['csrfToken' => csrf_token(),]); ?>
    </script>
</head>
<body>

<header class="colored" id="header">
  <a href="/"><img src="/postrium_white.png"></a>
  <div class="rightlinks">
    @if(!Auth::user())
    <a href="/register">Sign Up</a>
    <a href="/login">Login</a>
    @else
    <a href="/logout">Logout</a>
    <a href="/home">My Dashboard</a>

    @include('portal.notifications')

    @endif
  </div>
</header>

        <div style="min-height:100vh;">
        @yield('content')
        </div>

    <!-- Scripts -->
    <script src="/js/app.js"></script>
    @include('footer')
</body>
</html>
