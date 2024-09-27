@extends('layout')

@section('content')
    <div id="app">
        <app :comments="{{ \App\Models\Comment::all() }}"></app>
    </div>
@endsection
