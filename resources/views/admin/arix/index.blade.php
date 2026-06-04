@extends('layouts.arix', ['navbar' => 'index', 'sideEditor' => true])

@section('title')
    Arix Theme
@endsection

@section('content')
    <form action="{{ route('admin.arix') }}" method="POST">
        <div class="header">
            <p>General settings</p>
            <span class="description-text">Change the general settings of Arix Theme.</span>
        </div>
        <div class="input-field hr">
            <label for="arix:logo">Panel logo</label>
            <input type="text" id="arix:logo" name="arix:logo" value="{{ old('arix:logo', $logo) }}" />
        </div>
        <div class="input-field hr">
            <label for="arix:logoHeight">Panel logo height</label>
            <input type="text" id="arix:logoHeight" name="arix:logoHeight" value="{{ old('arix:logoHeight', $logoHeight) }}" />
        </div>
        <div class="input-field hr">
            <label for="arix:fullLogo">Logo only</label>
            <select name="arix:fullLogo" value="{{ old('arix:fullLogo', $fullLogo) }}">
                <option value="false">Disable</option>
                <option value="true" @if(old('arix:fullLogo', $fullLogo) == 'true') selected @endif>Enable</option>
            </select>
            <small>Enable or disable the text next to the panel logo.</small>
        </div>
        <div class="input-field hr">
            <label for="arix:discord">Discord ID</label>
            <input type="text" id="arix:discord" name="arix:discord" value="{{ old('arix:discord', $discord) }}" />
            <small>Leave empty remove the discord link from your panel</small>
        </div>
        <div class="input-field hr">
            <label for="arix:support">Supportcenter</label>
            <input type="text" id="arix:support" name="arix:support" value="{{ old('arix:support', $support) }}" />
            <small>Leave empty to remove the support link from your panel</small>
        </div>
        <div class="input-field hr">
            <label for="arix:status">Status page</label>
            <input type="text" id="arix:status" name="arix:status" value="{{ old('arix:status', $status) }}" />
            <small>Leave empty to remove the support link from your panel</small>
        </div>
        <div class="input-field hr">
            <label for="arix:billing">Billing area</label>
            <input type="text" id="arix:billing" name="arix:billing" value="{{ old('arix:billing', $billing) }}" />
            <small>Leave empty to remove the support link from your panel</small>
        </div>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection