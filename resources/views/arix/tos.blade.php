@extends('templates/base/core')

@section('title')
    Terms of Service
@endsection

@section('container')
    <div class="max-w-[1200px] mx-auto px-4 py-8">
        <h1 class="text-3xl font-semibold text-gray-50 mb-6">Terms of Service</h1>
        <div class="prose prose-invert max-w-none">
            {!! $tos_content !!}
        </div>
    </div>
@endsection
