@extends('app.layout')
@section('title','Retail Dashboard')

@section('content')
@include('app.partials.dashboard-content')
@endsection

@push('scripts')
@include('app.partials.dashboard-scripts')
@endpush
