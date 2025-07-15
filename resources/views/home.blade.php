@extends('layouts.master')

@section('title', 'Home | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    @include('partials.content')

@endsection
