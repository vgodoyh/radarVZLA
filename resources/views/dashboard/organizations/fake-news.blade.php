@extends('layouts.public')

@section('title', 'Observatorio Venezolano de Fake News | Pulso Venezuela')

@section('content')
    @include('dashboard.organizations.partials.panel', [
        'theme' => 'fake-news',
        'sectionLabel' => __('dashboard.fakenews_title'),
        'showStats' => false,
    ])
@endsection
