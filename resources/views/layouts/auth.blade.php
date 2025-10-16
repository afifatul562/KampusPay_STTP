<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Authentication') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Auth Styles -->
    <style>
        [x-cloak] { display: none !important; }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Figtree', sans-serif;
            color: #fff;
            background-color: #111;
        }

        .bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("{{ asset('images/background-login.jpg') }}") no-repeat center center;
            background-size: cover;
            filter: blur(4px) brightness(0.7);
            transform: scale(1.1);
        }

        .auth-container {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .auth-box {
            background: rgba(20, 20, 20, 0.85);
            padding: 40px 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 15px 25px rgba(0,0,0,.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .auth-box h1 {
            margin: 0 0 10px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .auth-box p {
            margin-bottom: 30px;
            color: #a0aec0;
        }

        .input-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.875rem;
            color: #a0aec0;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            background: #2d3748;
            border: 1px solid #4a5568;
            border-radius: 5px;
            outline: none;
            color: white;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .input-group input:focus {
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            user-select: none;
        }

        .btn-auth {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: #4a5568;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-auth:hover {
            background: #2d3748;
        }

        .error-message {
            color: #f56565;
            font-size: 0.875rem;
            margin-top: 5px;
            display: block;
        }

        .auth-links {
            margin-top: 20px;
            text-align: center;
        }

        .auth-links a {
            color: #a0aec0;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .auth-links a:hover {
            color: #4299e1;
        }
    </style>
</head>
<body>
    <div class="bg-container"></div>
    <div class="auth-container">
        <div class="auth-box">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
