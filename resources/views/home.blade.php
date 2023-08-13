@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center">
        <chat-box :auth="{{ auth()->user() }}"></chat-box>
    </div>
</div>
@endsection