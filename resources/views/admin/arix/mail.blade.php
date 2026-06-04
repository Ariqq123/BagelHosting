@extends('layouts.arix', ['navbar' => 'mail', 'sideEditor' => false])

@section('title')
    Arix Mail
@endsection

@section('content')
    <form action="{{ route('admin.arix.mail') }}" method="POST">
        <div class="content-box">
            <div class="header">
                <p>Mail settings</p>
                <span class="description-text">Change the mail template settings.</span>
            </div>

            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail primary color</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Set the mail primary color</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="color" name="arix:mail_color" value="{{ old('arix:mail_color', $mail_color) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail background color</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Set the mail background color</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="color" name="arix:mail_backgroundColor" value="{{ old('arix:mail_backgroundColor', $mail_backgroundColor) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail logo</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Set the mail logo</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_logo" value="{{ old('arix:mail_logo', $mail_logo) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail full logo</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Enable or disable mail full logo</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:mail_logoFull" value="{{ old('arix:mail_logoFull', $mail_logoFull) }}">
                            <option value="false">Disable</option>
                            <option value="true" @if(old('arix:mail_logoFull', $mail_logoFull) == 'true') selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail color mode</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Set color dark or light mode</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <select name="arix:mail_mode" value="{{ old('arix:mail_mode', $mail_mode) }}">
                            <option value="dark">Dark mode</option>
                            <option value="light" @if(old('arix:mail_mode', $mail_mode) == 'light') selected @endif>Light mode</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="header" style="margin-top:50px">
                <p>Mail socials settings</p>
                <span class="description-text">Change the mail socials settings.</span>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail discord</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave empty to remove it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_discord" value="{{ old('arix:mail_discord', $mail_discord) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail Twitter</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave empty to remove it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_twitter" value="{{ old('arix:mail_twitter', $mail_twitter) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail Facebook</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave empty to remove it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_facebook" value="{{ old('arix:mail_facebook', $mail_facebook) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail Instagram</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave empty to remove it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_instagram" value="{{ old('arix:mail_instagram', $mail_instagram) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail Linkedin</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave empty to remove it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_linkedin" value="{{ old('arix:mail_linkedin', $mail_linkedin) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail Youtube</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave empty to remove it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_youtube" value="{{ old('arix:mail_youtube', $mail_youtube) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail status page</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave empty to remove it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_status" value="{{ old('arix:mail_status', $mail_status) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail billing</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave empty to remove it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_billing" value="{{ old('arix:mail_billing', $mail_billing) }}" />
                    </div>
                </div>
            </div>
            <div class="row" style="padding-top:20px">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Mail support</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Leave empty to remove it</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field">
                        <input type="text" name="arix:mail_support" value="{{ old('arix:mail_support', $mail_support) }}" />
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