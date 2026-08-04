@extends('layouts.public')

@section('title', 'Justicia, Encuentro y Perdón | Pulso Venezuela')

@section('content')
    @include('dashboard.organizations.partials.panel', [
        'theme' => 'jep',
        'sectionLabel' => __('dashboard.jep_title'),
        'showStats' => true,
    ])
@endsection
