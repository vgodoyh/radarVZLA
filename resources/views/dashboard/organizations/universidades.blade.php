@extends('layouts.public')

@section('title', 'Observatorio de Universidades | Pulso Venezuela')

@section('content')
    @include('dashboard.organizations.partials.panel', [
        'theme' => 'obu',
        'sectionLabel' => __('dashboard.university_title'),
        'showStats' => false,
    ])
@endsection
