@extends('layouts.arix', ['navbar' => 'layout', 'sideEditor' => true])

@section('title')
    Arix Layout
@endsection

@section('content')

    <form action="{{ route('admin.arix.layout') }}" method="POST">
        <div class="header">
            <p>General layout settings</p>
            <span class="description-text">Change the general layout settings of Arix Theme.</span>
        </div>
        <div class="input-field input-options hr">
            <span>Layout</span>
            <div>
                <input type="radio" name="arix:layout" value="1" id="layout-1" {{ $layout == 1 ? "checked" : "" }}>
                <label for="layout-1">
                    <img src="/arix/layout-1.svg" />
                </label>
            </div>
            <div>
                <input type="radio" name="arix:layout" value="2" id="layout-2" {{ $layout == 2 ? "checked" : "" }}>
                <label for="layout-2">
                    <img src="/arix/layout-2.svg" />
                </label>
            </div>
            <div>
                <input type="radio" name="arix:layout" value="3" id="layout-3" {{ $layout == 3 ? "checked" : "" }}>
                <label for="layout-3">
                    <img src="/arix/layout-3.svg" />
                </label>
            </div>
            <div>
                <input type="radio" name="arix:layout" value="4" id="layout-4" {{ $layout == 4 ? "checked" : "" }}>
                <label for="layout-4">
                    <img src="/arix/layout-4.svg" />
                </label>
            </div>
            <div>
                <input type="radio" name="arix:layout" value="5" id="layout-5" {{ $layout == 5 ? "checked" : "" }}>
                <label for="layout-5">
                    <img src="/arix/layout-5.svg" />
                </label>
            </div>
        </div>
        <div class="input-field">
            <label for="arix:logoPosition">Search or select bar</label>
            <select name="arix:searchComponent" value="{{ old('arix:searchComponent', $searchComponent) }}">
                <option value="1">Server select bar</option>
                <option value="2" @if(old('arix:searchComponent', $searchComponent) == '2') selected @endif>Searchbar</option>
            </select>
            <small>Where do you want the logo on the login screen.</small>
        </div>
        <div class="header">
            <p>Login layout settings</p>
            <span class="description-text">Change the layout settings of the auth pages of Arix Theme.</span>
        </div>
        <div class="input-field input-options hr">
            <span>Login screen layout</span>
            <div>
                <input type="radio" name="arix:loginLayout" value="1" id="loginLayout-1" {{ $loginLayout == 1 ? "checked" : "" }}>
                <label for="loginLayout-1">
                    <img src="/arix/loginLayout-1.svg" />
                </label>
            </div>
            <div>
                <input type="radio" name="arix:loginLayout" value="2" id="loginLayout-2" {{ $loginLayout == 2 ? "checked" : "" }}>
                <label for="loginLayout-2">
                    <img src="/arix/loginLayout-2.svg" />
                </label>
            </div>
            <div>
                <input type="radio" name="arix:loginLayout" value="3" id="loginLayout-3" {{ $loginLayout == 3 ? "checked" : "" }}>
                <label for="loginLayout-3">
                    <img src="/arix/loginLayout-3.svg" />
                </label>
            </div>
            <div>
                <input type="radio" name="arix:loginLayout" value="4" id="loginLayout-4" {{ $loginLayout == 4 ? "checked" : "" }}>
                <label for="loginLayout-4">
                    <img src="/arix/loginLayout-4.svg" />
                </label>
            </div>
        </div>
        <div class="input-field hr">
            <label for="arix:socialPosition">Login social position</label>
            <select name="arix:socialPosition" value="{{ old('arix:socialPosition', $socialPosition) }}">
                <option value="1">Above form</option>
                <option value="2" @if(old('arix:socialPosition', $socialPosition) == '2') selected @endif>Under form</option>
            </select>
            <small>Where do you want the social buttons on the login screen.</small>
        </div>
        <div class="input-field">
            <label for="arix:logoPosition">Login logo position</label>
            <select name="arix:logoPosition" value="{{ old('arix:logoPosition', $logoPosition) }}">
                <option value="1">Above form</option>
                <option value="2" @if(old('arix:logoPosition', $logoPosition) == '2') selected @endif>Top corner</option>
            </select>
            <small>Where do you want the logo on the login screen.</small>
        </div>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection