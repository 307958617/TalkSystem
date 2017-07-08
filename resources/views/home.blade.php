@extends('layouts.app')

@section('content')
<div class="container">
    <chat user="{{ Auth::user() }}"></chat>
</div>

@endsection
