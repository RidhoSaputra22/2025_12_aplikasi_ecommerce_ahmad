@extends('layouts.app')

@section('content')
@include('layouts.navbar')

{{-- Content --}}
@include('components.product.banner')
@include('components.product.content', [
'products' => $products,
'categories' => $categories,
'request' => $request,
])

{{-- @include('components.home.faq') --}}


{{-- End Content --}}

@include('layouts.footter')
@endsection
