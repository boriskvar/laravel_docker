@extends('layout')

@section('content')
    <h1>Home</h1>

    <app :comments="{{ \App\Models\Comment::all() }}"></app>
@endsection
