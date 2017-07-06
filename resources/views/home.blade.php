@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <chat user="{{ Auth::user() }}"></chat>
        </div>
        <div class="col-md-4">
            <members></members>
        </div>
    </div>
</div>

@endsection
