@extends('layouts.arix', ['navbar' => 'colors', 'sideEditor' => false])

@section('title')
    Arix Colors
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="header">
                <p>Color settings</p>
                <span class="description-text">Utilize the Arix theme color picker to apply your color scheme effortlessly, revert to the default color settings, toggle between various input types, and explore our website's color scheme generator. Don't forget to save your changes!</span>
                <br/><br />
                <button onclick="toggleInputType()" class="button button-primary">Toggle Input Type</button>
            </div>
        </div>
    </div>
    <form action="{{ route('admin.arix.colors') }}" method="POST">
        <div class="row">
            <div class="col-md-8">
                <div class="content-box">
                    <div class="row">
                        <div class="col-md-12">
                            <p style="font-size:2.5rem;font-weight:bold;">Darkmode colors<p>
                            <p style="margin:0;font-weight:bold;">Primary color<p>
                            <small class="color-margin">Primary is the main color of your brand</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:primary">Premium color</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:primary" value="{{ old('arix:primary', $primary) }}" />
                                <button type="button" data-name="arix:primary" data-value="#4a35cf">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div style="margin:30px 0px;width:100%;height:1px;background-color:var(--gray500);"> </div>
                            <p style="margin:0;font-weight:bold;">Success colors<p>
                            <small class="color-margin">This are the colors of the green buttons</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:successText">Success text</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:successText" value="{{ old('arix:successText', $successText) }}" />
                                <button type="button" data-name="arix:successText" data-value="#e1ffd8">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:successBorder">Success border</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:successBorder" value="{{ old('arix:successBorder', $successBorder) }}" />
                                <button type="button" data-name="arix:successBorder" data-value="#56aa2b">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:successBackground">Success background color</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:successBackground" value="{{ old('arix:successBackground', $successBackground) }}" />
                                <button type="button" data-name="arix:successBackground" data-value="#3d8f1f">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div style="margin:30px 0px;width:100%;height:1px;background-color:var(--gray500);"> </div>
                            <p style="margin:0;font-weight:bold;">Danger colors<p>
                            <small class="color-margin">This are the colors of the red buttons</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:dangerText">Danger text</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:dangerText" value="{{ old('arix:dangerText', $dangerText) }}" />
                                <button type="button" data-name="arix:dangerText" data-value="#ffd8d8">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:dangerBorder">Danger border</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:dangerBorder" value="{{ old('arix:dangerBorder', $dangerBorder) }}" />
                                <button type="button" data-name="arix:dangerBorder" data-value="#aa2a2a">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:dangerBackground">Danger background color</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:dangerBackground" value="{{ old('arix:dangerBackground', $dangerBackground) }}" />
                                <button type="button" data-name="arix:dangerBackground" data-value="#8f1f20">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div style="margin:30px 0px;width:100%;height:1px;background-color:var(--gray500);"> </div>
                            <p style="margin:0;font-weight:bold;">Secondary colors<p>
                            <small class="color-margin">This are the colors of the gray buttons</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:secondaryText">Secondary text</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:secondaryText" value="{{ old('arix:secondaryText', $secondaryText) }}" />
                                <button type="button" data-name="arix:secondaryText" data-value="#b2b2c1">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:secondaryBorder">Secondary border</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:secondaryBorder" value="{{ old('arix:secondaryBorder', $secondaryBorder) }}" />
                                <button type="button" data-name="arix:secondaryBorder" data-value="#42425b">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:secondaryBackground">Secondary background color</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:secondaryBackground" value="{{ old('arix:secondaryBackground', $secondaryBackground) }}" />
                                <button type="button" data-name="arix:secondaryBackground" data-value="#2b2b40">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div style="margin:30px 0px;width:100%;height:1px;background-color:var(--gray500);"> </div>
                            <p style="margin:0;font-weight:bold;">Gray colors<p>
                            <small class="color-margin">This are the colors of the colors of the panel</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:gray50">Gray 50</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray50" value="{{ old('arix:gray50', $gray50) }}" />
                                <button type="button" data-name="arix:gray50" data-value="#f4f4f4">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color of the lightest text</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:gray100">Gray 100</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray100" value="{{ old('arix:gray100', $gray100) }}" />
                                <button type="button" data-name="arix:gray100" data-value="#d5d5db">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color of the light text</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:gray200">Gray 200</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray200" value="{{ old('arix:gray200', $gray200) }}" />
                                <button type="button" data-name="arix:gray200" data-value="#b2b2c1">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color of all regular text</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:gray300">Gray 300</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray300" value="{{ old('arix:gray300', $gray300) }}" />
                                <button type="button" data-name="arix:gray300" data-value="#8282a4">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color of all sub text</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:gray400">Gray 400</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray400" value="{{ old('arix:gray400', $gray400) }}" />
                                <button type="button" data-name="arix:gray400" data-value="#5e5e7f">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color for small details</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:gray500">Gray 500</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray500" value="{{ old('arix:gray500', $gray500) }}" />
                                <button type="button" data-name="arix:gray500" data-value="#42425b">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color used for border</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:gray600">Gray 600</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray600" value="{{ old('arix:gray600', $gray600) }}" />
                                <button type="button" data-name="arix:gray600" data-value="#2b2b40">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color used for the input</small>
                        </div> 
                        <div class="col-md-4 input-field">
                            <label for="arix:gray700">Gray 700</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray700" value="{{ old('arix:gray700', $gray700) }}" />
                                <button type="button" data-name="arix:gray700" data-value="#1d1d37">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color used for the boxes</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:gray800">Gray 800</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray800" value="{{ old('arix:gray800', $gray800) }}" />
                                <button type="button" data-name="arix:gray800" data-value="#0b0d2a">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color used for the background</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:gray900">Gray 900</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:gray900" value="{{ old('arix:gray900', $gray900) }}" />
                                <button type="button" data-name="arix:gray900" data-value="#040519">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color the darkest elements</small>
                        </div>
                    </div>
                </div>
            </div> 
            <div class="col-md-4">
                <div style="
                    background-color: {{ $gray800 }};
                    border-radius: 10px;
                    margin-top: 20px;
                    padding:25px;color:{{ $gray200 }} !important;
                ">
                    <div style="
                        font-size: 2rem;
                        font-weight: 400;
                        color: {{ $gray50 }};
                        display: flex;
                        align-items: center;
                        column-gap: 10px;
                    ">
                        <img src="/arix/Arix.png" style="width:32px"/>
                        Arix Theme
                    </div>
                    <div style="
                        display: flex;
                        align-items: center;
                        column-gap: 5px;
                        margin: 10px 0px;
                        font-size: 1.5rem;
                    ">
                        <i data-lucide="globe-2" style="width:20px;color:{{ $gray300 }}"></i> panel.weijers.one:25565
                    </div>
                    <hr style="border-color:{{ $gray500 }};"/>
                    <div style="
                        display: flex;
                        align-items: center;
                        column-gap: 10px;
                        margin-top: 10px;
                    ">
                        <div style="
                            background-color: {{ $successBackground }};
                            color: {{ $successText }};
                            border: 1px solid {{ $successBorder }};
                            padding: 7px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 100%;
                            border-radius: 5px;
                        ">
                            <i data-lucide="play" style="width:20px"></i>
                        </div>
                        <div style="
                            background-color: {{ $secondaryBackground }};
                            color: {{ $secondaryText }};
                            border: 1px solid {{ $secondaryBorder }};
                            padding: 7px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 100%;
                            border-radius: 5px;
                        ">
                            <i data-lucide="rotate-ccw" style="width:20px"></i>
                        </div>
                        <div style="
                            background-color: {{ $dangerBackground }};
                            color: {{ $dangerText }};
                            border: 1px solid {{ $dangerBorder }};
                            padding: 7px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 100%;
                            border-radius: 5px;
                        ">
                            <i data-lucide="stop-circle" style="width:20px"></i>
                        </div>
                    </div>
                    <div style="
                        margin:20px 0;
                        background-color: {{ $gray700 }};
                        padding:15px;
                        border-radius:7px;
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                    ">
                        <div>    
                            <span style="color: {{ $gray300 }}">CPU Usage: </span>
                            <div style="
                                display:flex;
                                align-items:center;
                                column-gap:5px;
                            ">
                                <span style="font-size:2rem;font-weight:500;color:{{ $gray50 }}">20.4%</span>
                                <span>/ 100% </span>
                            </div>
                        </div>
                        <div style="
                            width: 50px;
                            height: 50px;
                            background-color: {{ $primary }};
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            border-radius: 5px;
                        ">
                            <i data-lucide="cpu" style="width:30px"></i>
                        </div>
                    </div>
                    <div style="
                        margin:20px 0;
                        background-color: {{ $gray700 }};
                        padding:15px;
                        border-radius:7px;
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                    ">
                        <div>    
                            <span style="color: {{ $gray300 }}">Memory Usage: </span>
                            <div style="
                                display:flex;
                                align-items:center;
                                column-gap:5px;
                            ">
                                <span style="font-size:2rem;font-weight:500;color:{{ $gray50 }}">20 GB</span>
                                <span>/ 40 GB </span>
                            </div>
                        </div>
                        <div style="
                            width: 50px;
                            height: 50px;
                            background-color: {{ $primary }};
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            border-radius: 5px;
                        ">
                            <i data-lucide="memory-stick" style="width:30px"></i>
                        </div>
                    </div>
                    <div style="
                        background-color: {{ $primary }};
                        color: white;
                        padding:10px 15px;
                        text-align:center;
                        border-radius:5px;
                        font-weight:500;
                    ">Example button</div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="content-box">
                    <div class="row">
                        <div class="col-md-12">
                            <p style="font-size:2.5rem;font-weight:bold;">Lightmode colors<p>
                            <p style="margin:0;font-weight:bold;">Primary color<p>
                            <small class="color-margin">Primary is the main color of your brand</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_primary">Premium color</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_primary" value="{{ old('arix:lightmode_primary', $lightmode_primary) }}" />
                                <button type="button" data-name="arix:lightmode_primary" data-value="#4a35cf">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div style="margin:30px 0px;width:100%;height:1px;background-color:var(--gray500);"> </div>
                            <p style="margin:0;font-weight:bold;">Success colors<p>
                            <small class="color-margin">This are the colors of the green buttons</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_successText">Success text</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_successText" value="{{ old('arix:lightmode_successText', $lightmode_successText) }}" />
                                <button type="button" data-name="arix:lightmode_successText" data-value="#e1ffd8">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_successBorder">Success border</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_successBorder" value="{{ old('arix:lightmode_successBorder', $lightmode_successBorder) }}" />
                                <button type="button" data-name="arix:lightmode_successBorder" data-value="#56aa2b">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_successBackground">Success background color</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_successBackground" value="{{ old('arix:lightmode_successBackground', $lightmode_successBackground) }}" />
                                <button type="button" data-name="arix:lightmode_successBackground" data-value="#3d8f1f">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div style="margin:30px 0px;width:100%;height:1px;background-color:var(--gray500);"> </div>
                            <p style="margin:0;font-weight:bold;">Danger colors<p>
                            <small class="color-margin">This are the colors of the red buttons</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_dangerText">Danger text</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_dangerText" value="{{ old('arix:lightmode_dangerText', $lightmode_dangerText) }}" />
                                <button type="button" data-name="arix:lightmode_dangerText" data-value="#ffd8d8">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_dangerBorder">Danger border</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_dangerBorder" value="{{ old('arix:lightmode_dangerBorder', $lightmode_dangerBorder) }}" />
                                <button type="button" data-name="arix:lightmode_dangerBorder" data-value="#aa2a2a">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_dangerBackground">Danger background color</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_dangerBackground" value="{{ old('arix:lightmode_dangerBackground', $lightmode_dangerBackground) }}" />
                                <button type="button" data-name="arix:lightmode_dangerBackground" data-value="#8f1f20">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div style="margin:30px 0px;width:100%;height:1px;background-color:var(--gray500);"> </div>
                            <p style="margin:0;font-weight:bold;">Secondary colors<p>
                            <small class="color-margin">This are the colors of the gray buttons</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_secondaryText">Secondary text</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_secondaryText" value="{{ old('arix:lightmode_secondaryText', $lightmode_secondaryText) }}" />
                                <button type="button" data-name="arix:lightmode_secondaryText" data-value="#46464d">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_secondaryBorder">Secondary border</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_secondaryBorder" value="{{ old('arix:lightmode_secondaryBorder', $lightmode_secondaryBorder) }}" />
                                <button type="button" data-name="arix:lightmode_secondaryBorder" data-value="#c0c0d3">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_secondaryBackground">Secondary background color</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_secondaryBackground" value="{{ old('arix:lightmode_secondaryBackground', $lightmode_secondaryBackground) }}" />
                                <button type="button" data-name="arix:lightmode_secondaryBackground" data-value="#a6a7bd">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div style="margin:30px 0px;width:100%;height:1px;background-color:var(--gray500);"> </div>
                            <p style="margin:0;font-weight:bold;">Gray colors<p>
                            <small class="color-margin">This are the colors of the colors of the panel</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray50">Gray 50</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray50" value="{{ old('arix:lightmode_gray50', $lightmode_gray50) }}" />
                                <button type="button" data-name="arix:lightmode_gray50" data-value="#141415">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color of the lightest text</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray100">Gray 100</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray100" value="{{ old('arix:lightmode_gray100', $lightmode_gray100) }}" />
                                <button type="button" data-name="arix:lightmode_gray100" data-value="#27272c">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color of the light text</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray200">Gray 200</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray200" value="{{ old('arix:lightmode_gray200', $lightmode_gray200) }}" />
                                <button type="button" data-name="arix:lightmode_gray200" data-value="#46464d">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color of all regular text</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray300">Gray 300</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray300" value="{{ old('arix:lightmode_gray300', $lightmode_gray300) }}" />
                                <button type="button" data-name="arix:lightmode_gray300" data-value="#626272">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color of all sub text</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray400">Gray 400</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray400" value="{{ old('arix:lightmode_gray400', $lightmode_gray400) }}" />
                                <button type="button" data-name="arix:lightmode_gray400" data-value="#757689">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">The color for small details</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray500">Gray 500</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray500" value="{{ old('arix:lightmode_gray500', $lightmode_gray500) }}" />
                                <button type="button" data-name="arix:lightmode_gray500" data-value="#a6a7bd">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color used for border</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray600">Gray 600</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray600" value="{{ old('arix:lightmode_gray600', $lightmode_gray600) }}" />
                                <button type="button" data-name="arix:lightmode_gray600" data-value="#c0c0d3">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color used for the input</small>
                        </div> 
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray700">Gray 700</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray700" value="{{ old('arix:lightmode_gray700', $lightmode_gray700) }}" />
                                <button type="button" data-name="arix:lightmode_gray700" data-value="#e7e7ef">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color used for the boxes</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray800">Gray 800</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray800" value="{{ old('arix:lightmode_gray800', $lightmode_gray800) }}" />
                                <button type="button" data-name="arix:lightmode_gray800" data-value="#f0f1f5">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color used for the background</small>
                        </div>
                        <div class="col-md-4 input-field">
                            <label for="arix:lightmode_gray900">Gray 900</label>
                            <div class="input-w-reset">
                                <input type="color" class="form-control" name="arix:lightmode_gray900" value="{{ old('arix:lightmode_gray900', $lightmode_gray900) }}" />
                                <button type="button" data-name="arix:lightmode_gray900" data-value="#ffffff">
                                    <i data-lucide="rotate-ccw"></i>
                                </button>
                            </div>
                            <small class="color-margin">Color the darkest elements</small>
                        </div>
                    </div>
                </div>
            </div> 
            <div class="col-md-4">
                <div style="
                    background-color: {{ $lightmode_gray800 }};
                    border-radius: 10px;
                    margin-top: 20px;
                    padding:25px;color:{{ $lightmode_gray200 }} !important;
                ">
                    <div style="
                        font-size: 2rem;
                        font-weight: 400;
                        color: {{ $lightmode_gray50 }};
                        display: flex;
                        align-items: center;
                        column-gap: 10px;
                    ">
                        <img src="/arix/Arix.png" style="width:32px"/>
                        Arix Theme
                    </div>
                    <div style="
                        display: flex;
                        align-items: center;
                        column-gap: 5px;
                        margin: 10px 0px;
                        font-size: 1.5rem;
                    ">
                        <i data-lucide="globe-2" style="width:20px;color:{{ $lightmode_gray300 }}"></i> panel.weijers.one:25565
                    </div>
                    <hr style="border-color:{{ $lightmode_gray500 }};"/>
                    <div style="
                        display: flex;
                        align-items: center;
                        column-gap: 10px;
                        margin-top: 10px;
                    ">
                        <div style="
                            background-color: {{ $lightmode_successBackground }};
                            color: {{ $lightmode_successText }};
                            border: 1px solid {{ $lightmode_successBorder }};
                            padding: 7px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 100%;
                            border-radius: 5px;
                        ">
                            <i data-lucide="play" style="width:20px"></i>
                        </div>
                        <div style="
                            background-color: {{ $lightmode_secondaryBackground }};
                            color: {{ $lightmode_secondaryText }};
                            border: 1px solid {{ $lightmode_secondaryBorder }};
                            padding: 7px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 100%;
                            border-radius: 5px;
                        ">
                            <i data-lucide="rotate-ccw" style="width:20px"></i>
                        </div>
                        <div style="
                            background-color: {{ $lightmode_dangerBackground }};
                            color: {{ $lightmode_dangerText }};
                            border: 1px solid {{ $lightmode_dangerBorder }};
                            padding: 7px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 100%;
                            border-radius: 5px;
                        ">
                            <i data-lucide="stop-circle" style="width:20px"></i>
                        </div>
                    </div>
                    <div style="
                        margin:20px 0;
                        background-color: {{ $lightmode_gray700 }};
                        padding:15px;
                        border-radius:7px;
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                    ">
                        <div>    
                            <span style="color: {{ $lightmode_gray300 }}">CPU Usage: </span>
                            <div style="
                                display:flex;
                                align-items:center;
                                column-gap:5px;
                            ">
                                <span style="font-size:2rem;font-weight:500;color:{{ $lightmode_gray50 }}">20.4%</span>
                                <span>/ 100% </span>
                            </div>
                        </div>
                        <div style="
                            width: 50px;
                            height: 50px;
                            background-color: {{ $lightmode_primary }};
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            border-radius: 5px;
                        ">
                            <i data-lucide="cpu" style="width:30px"></i>
                        </div>
                    </div>
                    <div style="
                        margin:20px 0;
                        background-color: {{ $lightmode_gray700 }};
                        padding:15px;
                        border-radius:7px;
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                    ">
                        <div>    
                            <span style="color: {{ $lightmode_gray300 }}">Memory Usage: </span>
                            <div style="
                                display:flex;
                                align-items:center;
                                column-gap:5px;
                            ">
                                <span style="font-size:2rem;font-weight:500;color:{{ $lightmode_gray50 }}">20 GB</span>
                                <span>/ 40 GB </span>
                            </div>
                        </div>
                        <div style="
                            width: 50px;
                            height: 50px;
                            background-color: {{ $lightmode_primary }};
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            border-radius: 5px;
                        ">
                            <i data-lucide="memory-stick" style="width:30px"></i>
                        </div>
                    </div>
                    <div style="
                        background-color: {{ $lightmode_primary }};
                        color: white;
                        padding:10px 15px;
                        text-align:center;
                        border-radius:5px;
                        font-weight:500;
                    ">Example button</div>
                </div>
            </div>
        </div>
        <div class="floating-button-2">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
    <script>
    var resetButtons = document.querySelectorAll('button[data-name]');

    resetButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var inputName = button.getAttribute('data-name');
            var inputElement = document.querySelector('input[name="' + inputName + '"]');
            var value = button.getAttribute('data-value');

            inputElement.value = value;

            console.log('Input value for', inputName, 'reset to:', value);
        });
    });

    function toggleInputType() {
        var inputs = document.querySelectorAll('input');
        inputs.forEach(function(input) {
            if (input.type === 'text') {
            input.type = 'color';
            } else if (input.type === 'color') {
            input.type = 'text';
            }
        });
    }
    </script>
@endsection