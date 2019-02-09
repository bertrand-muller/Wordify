@extends('layouts.welcome')

@section('content')
    <img src="{{ asset(config('newlook.logos.notext')) }}" style="position: relative;"/>
    <div class="title m-b-md" style="position: relative; width: 100%; left: 0; color: white;">
        <b>{{ config('app.name') }}</b>
    </div>
    <br>
    <div class="m-b-md" style="position: relative; width: 100%; left: 0; color: white;">
        Sample users<br/>
        Admin user: admin.laravel@labs64.com / password: admin<br/>
        Demo user: demo.laravel@labs64.com / password: demo
    </div>
@endsection