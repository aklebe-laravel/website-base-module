@extends('website-base::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {!! config('website-base.name') !!}
    </p>
@endsection
