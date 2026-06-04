@extends('layouts.arix', ['navbar' => 'advanced', 'sideEditor' => false])

@section('title')
    Arix Advanced
@endsection

@section('content')
    <form action="{{ route('admin.arix.advanced') }}" method="POST">
        <div class="content-box">
            <div class="header">
                <p>Advanced settings</p>
                <span class="description-text">Change Arix advanced settings.</span>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Profile type</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Choose profile picture type</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:profileType" value="{{ old('arix:profileType', $profileType) }}">
                            <option value="boring">Boring Avatars</option>
                            <option value="avataaars" @if(old('arix:profileType', $profileType) == 'avataaars') selected @endif>Avataaars Neutral</option>
                            <option value="bottts" @if(old('arix:profileType', $profileType) == 'bottts') selected @endif>Bottts Neutral</option>
                            <option value="identicon" @if(old('arix:profileType', $profileType) == 'identicon') selected @endif>Identicon</option>
                            <option value="initials" @if(old('arix:profileType', $profileType) == 'initials') selected @endif>Initials</option>
                            <option value="gravatar" @if(old('arix:profileType', $profileType) == 'gravatar') selected @endif>Gravatar</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">IP Flag</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Show flags with your IP</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:ipFlag" value="{{ old('arix:ipFlag', $ipFlag) }}">
                            <option value="false">Disable</option>
                            <option value="true" @if(old('arix:ipFlag', $ipFlag) == 'true') selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Low Resources Alert</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Enable or disable low resources alert</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:lowResourcesAlert" value="{{ old('arix:lowResourcesAlert', $lowResourcesAlert) }}">
                            <option value="false">Disable</option>
                            <option value="true" @if(old('arix:lowResourcesAlert', $lowResourcesAlert) == 'true') selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mode toggler</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Enable or disable mode toggler</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:modeToggler" value="{{ old('arix:modeToggler', $modeToggler) }}">
                            <option value="false">Disable</option>
                            <option value="true" @if(old('arix:modeToggler', $modeToggler) == 'true') selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="padding-top:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Language switcher</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Enable or disable language switcher</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:langSwitch" value="{{ old('arix:langSwitch', $langSwitch) }}">
                            <option value="false">Disable</option>
                            <option value="true" @if(old('arix:langSwitch', $langSwitch) == 'true') selected @endif>Enable</option>
                        </select>
                    </div>
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