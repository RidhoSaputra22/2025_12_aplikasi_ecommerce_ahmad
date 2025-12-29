@extends('layouts.app')

@section('content')
@include('layouts.navbar')

{{-- Content --}}
@include('components.product.banner')
@include('components.product.content')

{{-- @include('components.home.faq') --}}


{{-- End Content --}}

@include('layouts.footter')
@endsection
