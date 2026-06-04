@extends('layouts.arix', ['navbar' => 'styling', 'sideEditor' => false])

@section('title')
    Arix Styling
@endsection

@section('content')

    <form action="{{ route('admin.arix.styling') }}" method="POST">
        <div class="content-box">
            <div class="header">
                <p>Styling settings</p>
                <span class="description-text">Customize the general appears of Arix Theme.</span>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Page titles</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Enable or disable page titles</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:pageTitle" value="{{ old('arix:pageTitle', $pageTitle) }}">
                            <option value="false">Disable</option>
                            <option value="true" @if(old('arix:pageTitle', $pageTitle) == 'true') selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Flash message</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Choose between different flash message styles</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field input-options" style="display:flex;column-gap:5px">
                        <div style="width:100%;">
                            <input type="radio" name="arix:flashMessage" value="1" id="flashMessage-1" {{ $flashMessage == 1 ? "checked" : "" }}>
                            <label for="flashMessage-1">
                                Alert at right top corner
                            </label>
                        </div>
                        <div style="width:100%;">
                            <input type="radio" name="arix:flashMessage" value="2" id="flashMessage-2" {{ $flashMessage == 2 ? "checked" : "" }}>
                            <label for="flashMessage-2">
                                Alert at bottom center
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Background image</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave value empty to disable it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:backgroundImage" value="{{ old('arix:backgroundImage', $backgroundImage) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Background image light mode</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave value empty to disable it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:backgroundImageLight" value="{{ old('arix:backgroundImageLight', $backgroundImageLight) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Login background image</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave value empty to disable it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:loginBackground" value="{{ old('arix:loginBackground', $loginBackground) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Login overlay gradient</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Enable or disable a static gradient.</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:loginGradient" value="{{ old('arix:loginGradient', $loginGradient) }}">
                            <option value="false">Disable</option>
                            <option value="true" @if(old('arix:logoPosition', $loginGradient) == 'true') selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Backdrop blur</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Enable or disable backdrop blur</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:backdrop" value="{{ old('arix:backdrop', $backdrop) }}">
                            <option value="false">Disable</option>
                            <option value="true" @if(old('arix:backdrop', $backdrop) == 'true') selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Components opacity</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Change the components opacity.</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:backdropPercentage" value="{{ old('arix:backdropPercentage', $backdropPercentage) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Default color mode</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Change the default color mode</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:defaultMode" value="{{ old('arix:defaultMode', $defaultMode) }}">
                            <option value="lightmode">Lightmode</option>
                            <option value="darkmode" @if(old('arix:defaultMode', $defaultMode) == 'darkmode') selected @endif>Darkmode</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Input/Button border radius</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Change the input/button border radius.</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:radiusInput" value="{{ old('arix:radiusInput', $radiusInput) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Box border radius</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Change the box border radius.</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:radiusBox" value="{{ old('arix:radiusBox', $radiusBox) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Input border</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Enable or disable input border</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:borderInput" value="{{ old('arix:borderInput', $borderInput) }}">
                            <option value="false">Disable</option>
                            <option value="true" @if(old('arix:borderInput', $borderInput) == 'true') selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="padding-top:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Copyright text</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">For styling use BBCode format.</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:copyright" value="{{ old('arix:copyright', $copyright) }}" />
                    </div>
                </div>
            </div>
        </div>
        <div class="floating-button-2">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection