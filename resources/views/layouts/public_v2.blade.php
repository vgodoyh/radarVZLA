<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="analytics-navigation-endpoint" content="{{ route('analytics.navigation.store') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">

    @php
        $seoTitle = trim($__env->yieldContent('title')) ?: config('app.name', 'Pulso Venezuela');
        $seoDescription = trim($__env->yieldContent('description')) ?: __('dashboard.hero_description');
        $seoImage = trim($__env->yieldContent('og_image')) ?: asset('assets/img/pulso-venezuela-color.png');
        $seoUrl = url()->current();
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $seoUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ __('dashboard.site_name') }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:image" content="{{ $seoImage }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app_public.css', 'resources/css/public-hero-v2.css', 'resources/js/app_public.js'])
</head>
<body>
    @yield('content')
</body>
</html>
