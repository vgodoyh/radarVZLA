@extends('layouts.public')

@section('title', 'Acceso a la Justicia | Pulso Venezuela')

@section('content')
    @include('dashboard.organizations.partials.panel', [
        'theme' => 'acceso',
        'sectionLabel' => '#AlertaLegal',
        'showStats' => false,
        'searchable' => true,
    ])
@endsection
