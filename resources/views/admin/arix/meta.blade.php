@extends('layouts.arix', ['navbar' => 'meta', 'sideEditor' => true])

@section('title')
    Arix Meta data
@endsection

@section('content')

    <form action="{{ route('admin.arix.meta') }}" method="POST">
        <div class="header">
            <p>Meta Data settings</p>
            <span class="description-text">Change the meta data settings of Arix Theme.</span>
        </div>
        <div class="input-field hr">
            <label for="arix:meta_favicon">Favicon</label>
            <input type="text" id="arix:meta_favicon" name="arix:meta_favicon" value="{{ old('arix:meta_favicon', $meta_favicon) }}" />
        </div>
        <div class="input-field hr">
            <label for="arix:meta_title">Meta title</label>
            <input type="text" id="arix:meta_title" name="arix:meta_title" value="{{ old('arix:meta_title', $meta_title) }}" />
        </div>
        <div class="input-field hr">
            <label for="arix:meta_image">Meta image</label>
            <input type="text" id="arix:meta_image" name="arix:meta_image" value="{{ old('arix:meta_image', $meta_image) }}" />
        </div>
        <div class="input-field">
            <label for="arix:meta_description">Meta description</label>
            <textarea type="text" id="arix:meta_description" name="arix:meta_description" width="100%" rows="5">{{ old('arix:meta_description', $meta_description) }}</textarea>
        </div>
        <div class="input-field hr">
            <label for="arix:meta_color">Meta color</label>
            <input type="color" id="arix:meta_color" name="arix:meta_color" value="{{ old('arix:meta_color', $meta_color) }}" />
        </div>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection