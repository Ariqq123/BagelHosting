@extends('layouts.admin')
<?php 
    // Define extension information.
    $EXTENSION_ID = "freeservers";
    $EXTENSION_NAME = stripslashes("Free Servers");
    $EXTENSION_VERSION = "1.0.0";
    $EXTENSION_DESCRIPTION = stripslashes("Allows admins to offer free servers to users.");
    $EXTENSION_ICON = "/assets/extensions/freeservers/icon.jpg";
    $EXTENSION_WEBSITE = "[website]";
    $EXTENSION_WEBICON = "[webicon]";
?>
@include('blueprint.admin.template')

@section('title')
    {{ $EXTENSION_NAME }}
@endsection

@section('content-header')
    @yield('extension.header')
@endsection

@section('content')
    @yield('extension.config')
    @yield('extension.description')@section('title')
    {{ $lang['admin_title'] ?? 'Free Servers' }}
@endsection

@section('content-header')
    <h1>{{ $lang['admin_title'] ?? 'Free Servers' }}<small>{{ $lang['admin_subtitle'] ?? 'Offer free servers to users' }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">{{ $lang['breadcrumb_admin'] ?? 'Admin' }}</a></li>
        <li><a href="{{ route('admin.extensions') }}">{{ $lang['breadcrumb_extensions'] ?? 'Extensions' }}</a></li>
        <li class="active">{{ $lang['breadcrumb_freeservers'] ?? 'Free Servers' }}</li>
    </ol>
@endsection

@section('content')

<style>
/* === Free Servers Admin — Blueprint Dark Theme === */
.fs-wrap .box,
.fs-wrap .nav-tabs-custom {
    background: #29343e;
    border: 1px solid #3d4d5c;
    box-shadow: none;
}
.fs-wrap .box .box-header.with-border {
    background: #222d38;
    border-bottom: 1px solid #3d4d5c;
    color: #cad1d8;
}
.fs-wrap .box .box-title { color: #cad1d8; }
.fs-wrap .box-body { color: #cad1d8; }
.fs-wrap .box-footer {
    background: #222d38;
    border-top: 1px solid #3d4d5c;
}
.fs-wrap .form-control {
    background: #1f2933;
    border: 1px solid #3d4d5c;
    color: #cad1d8;
}
.fs-wrap .form-control:focus {
    border-color: #3c8dbc;
    background: #253040;
    color: #fff;
    box-shadow: none;
}
.fs-wrap select.form-control option,
.fs-wrap select.form-control optgroup { background: #1f2933; color: #cad1d8; }
.fs-wrap .input-group-addon {
    background: #222d38;
    border-color: #3d4d5c;
    color: #8fa3b1;
}
.fs-wrap label,
.fs-wrap .control-label { color: #b8c7ce; }
.fs-wrap .text-muted, .fs-wrap small.text-muted { color: #6d8492 !important; }
.fs-wrap hr { border-color: #3d4d5c; }
.fs-wrap h4 { color: #cad1d8; }
.fs-wrap .callout {
    background: #1f2933;
    border-left-color: #3c8dbc;
    color: #9ab0be;
}
.fs-wrap .callout.callout-info { border-left-color: #00c0ef; }
.fs-wrap .callout p { color: #9ab0be; }
/* Tabs */
.fs-wrap .nav-tabs-custom > .nav-tabs { background: #1f2933; border-bottom-color: #3d4d5c; }
.fs-wrap .nav-tabs-custom > .nav-tabs > li > a { color: #8fa3b1; background: transparent; border-color: transparent; }
.fs-wrap .nav-tabs-custom > .nav-tabs > li > a:hover { color: #cad1d8; background: #253040; }
.fs-wrap .nav-tabs-custom > .nav-tabs > li.active > a,
.fs-wrap .nav-tabs-custom > .nav-tabs > li.active > a:hover {
    color: #cad1d8;
    background: #29343e;
    border-bottom-color: #29343e;
}
.fs-wrap .nav-tabs-custom > .tab-content { background: #29343e; }
.fs-wrap .fs-tab-footer {
    padding: 10px 15px;
    border-top: 1px solid #3d4d5c;
    background: #222d38;
    text-align: right;
}
/* Tables */
.fs-wrap .table { color: #cad1d8; }
.fs-wrap .table > thead > tr > th { color: #8fa3b1; border-bottom: 2px solid #3d4d5c; background: #222d38; }
.fs-wrap .table > tbody > tr > td { border-top-color: #3d4d5c; }
.fs-wrap .table-striped > tbody > tr:nth-of-type(odd) { background: #222d38; }
.fs-wrap .table-hover > tbody > tr:hover { background: #2c3a47; }
/* Add-form well / search well */
.fs-wrap .fs-well {
    padding: 12px 15px;
    background: #1f2933;
    border: 1px solid #3d4d5c;
    border-radius: 4px;
    margin-bottom: 15px;
}
/* Checkbox */
.fs-wrap .checkbox label { color: #b8c7ce; pointer-events: auto; }
.fs-wrap .checkbox input[type="checkbox"] { pointer-events: auto; cursor: pointer; }
/* Callout — force readable text */
.fs-wrap .callout,
.fs-wrap .callout p,
.fs-wrap .callout h4,
.fs-wrap .callout small { color: #9ab0be !important; }
.fs-wrap .callout.callout-info { background: #1e2d3b !important; border-left: 5px solid #00c0ef; }
/* Node checkbox rows */
.fs-wrap .node-check { padding: 6px 0; }
.fs-wrap .node-check label { color: #cad1d8; font-weight: normal; cursor: pointer; }
.fs-wrap .node-check input[type="checkbox"] { margin-right: 6px; cursor: pointer; vertical-align: middle; }
/* User search dropdown */
#user-search-results .user-result:hover { background: #2c3a47; }
#user-search-results { background: #29343e; border: 1px solid #3d4d5c; border-top: none; }
#user-search-results .fs-no-results { padding: 8px 12px; color: #8fa3b1; }
</style>

@php
    $isEnabled = isset($settings) && $settings && $settings->enabled == 1;
    $allowedNodeIds = [];
    if (isset($settings) && $settings && $settings->allowed_nodes) {
        $decoded = json_decode($settings->allowed_nodes, true);
        if (is_array($decoded)) {
            $allowedNodeIds = array_map('strval', $decoded);
        }
    }
    $L = $lang ?? [];
    function fs_t($L, $key, $fallback = '') { return $L[$key] ?? $fallback; }
@endphp

@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-times-circle"></i> {{ session('error') }}
</div>
@endif
@if(isset($error) && $error)
<div class="alert alert-danger">
    <i class="fa fa-exclamation-triangle"></i> {{ $error }}
</div>
@endif

@if(isset($settings) && $settings)
<div class="fs-wrap">

{{-- ===== STATS ROW ===== --}}
<div class="row">
    <div class="col-lg-3 col-sm-6">
        <div class="small-box {{ $isEnabled ? 'bg-green' : 'bg-red' }}">
            <div class="inner">
                <h3>{{ $isEnabled ? fs_t($L, 'status_on', 'ON') : fs_t($L, 'status_off', 'OFF') }}</h3>
                <p>{{ fs_t($L, 'stat_system_status', 'System Status') }}</p>
            </div>
            <div class="icon"><i class="fa fa-power-off"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ $freeServersCount ?? 0 }}</h3>
                <p>{{ fs_t($L, 'stat_active_servers', 'Active Free Servers') }}</p>
            </div>
            <div class="icon"><i class="fa fa-server"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $usersWithFreeServers ?? 0 }}</h3>
                <p>{{ fs_t($L, 'stat_users_with_servers', 'Users with Free Servers') }}</p>
            </div>
            <div class="icon"><i class="fa fa-users"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>{{ isset($allowedEggs) ? $allowedEggs->count() : 0 }}</h3>
                <p>{{ fs_t($L, 'stat_allowed_eggs', 'Allowed Eggs') }}</p>
            </div>
            <div class="icon"><i class="fa fa-puzzle-piece"></i></div>
        </div>
    </div>
</div>

{{-- ===== OPTIONAL STATS DASHBOARD ===== --}}
@if($settings && $settings->enable_stats && !empty($stats))
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> {{ fs_t($L, 'stats_dashboard_title', 'Statistics Dashboard') }}</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-server"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ fs_t($L, 'stats_total_servers', 'Total Servers') }}</span>
                                <span class="info-box-number">{{ $stats['total_servers'] ?? 0 }}</span>
                            </div></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ fs_t($L, 'stats_active_now', 'Active Now') }}</span>
                                <span class="info-box-number">{{ $stats['active_servers'] ?? 0 }}</span>
                            </div></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-calendar-plus-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ fs_t($L, 'stats_created_today', 'Created Today') }}</span>
                                <span class="info-box-number">{{ $stats['today'] ?? 0 }}</span>
                            </div></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box"><span class="info-box-icon bg-red"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ fs_t($L, 'stats_expiring_soon', 'Expiring (7d)') }}</span>
                                <span class="info-box-number">{{ $stats['expiring_soon'] ?? 0 }}</span>
                            </div></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="box"><div class="box-header with-border"><h3 class="box-title"><i class="fa fa-hdd-o"></i> {{ fs_t($L, 'stats_resources', 'Resources in Use') }}</h3></div>
                            <div class="box-body">
                                <dl class="dl-horizontal" style="margin:0;">
                                    <dt>{{ fs_t($L, 'stats_memory', 'Memory') }}</dt><dd>{{ $stats['resources']['total_memory_gb'] ?? 0 }} GB</dd>
                                    <dt>{{ fs_t($L, 'stats_disk', 'Disk') }}</dt><dd>{{ $stats['resources']['total_disk_gb'] ?? 0 }} GB</dd>
                                    <dt>{{ fs_t($L, 'stats_avg_cpu', 'Avg CPU') }}</dt><dd>{{ $stats['resources']['avg_cpu'] ?? 0 }}%</dd>
                                </dl>
                            </div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="box"><div class="box-header with-border"><h3 class="box-title"><i class="fa fa-trophy"></i> {{ fs_t($L, 'stats_top_users', 'Top Users') }}</h3></div>
                            <div class="box-body">
                                @if(!empty($stats['top_users']))
                                    <table class="table table-condensed" style="margin:0;">
                                        @foreach($stats['top_users'] as $u)
                                        <tr><td>{{ $u['username'] }}</td><td class="text-right"><span class="badge bg-green">{{ $u['count'] }}</span></td></tr>
                                        @endforeach
                                    </table>
                                @else
                                    <p class="text-muted" style="margin:0;">{{ fs_t($L, 'stats_no_data', 'No data yet') }}</p>
                                @endif
                            </div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ===== SETTINGS (TABBED) — form wraps entire nav-tabs-custom ===== --}}
<div class="row">
    <div class="col-xs-12">
        <form method="POST" action="{{ $root }}">
            @csrf
            <input type="hidden" name="action" value="save_settings">

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab-general"    data-toggle="tab"><i class="fa fa-cog"></i>      {{ fs_t($L, 'tab_general',    'General') }}</a></li>
                    <li><a href="#tab-resources"  data-toggle="tab"><i class="fa fa-microchip"></i> {{ fs_t($L, 'tab_resources',  'Resources') }}</a></li>
                    <li><a href="#tab-expiration" data-toggle="tab"><i class="fa fa-clock-o"></i>   {{ fs_t($L, 'tab_expiration', 'Expiration') }}</a></li>
                    <li><a href="#tab-discord"    data-toggle="tab"><i class="fa fa-comments"></i>  {{ fs_t($L, 'tab_discord',    'Discord') }}</a></li>
                    <li><a href="#tab-nodes"      data-toggle="tab"><i class="fa fa-sitemap"></i>   {{ fs_t($L, 'tab_nodes',      'Nodes') }}</a></li>
                </ul>

                <div class="tab-content">

                    {{-- TAB: GENERAL --}}
                    <div class="tab-pane active" id="tab-general">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">{{ fs_t($L, 'setting_enable_system', 'Enable Free Servers') }}</label>
                                <div>
                                    <select name="enabled" class="form-control" style="width:180px;">
                                        <option value="1" {{ $isEnabled ? 'selected' : '' }}>✓ {{ fs_t($L, 'setting_enabled', 'Enabled') }}</option>
                                        <option value="0" {{ !$isEnabled ? 'selected' : '' }}>✗ {{ fs_t($L, 'setting_disabled', 'Disabled') }}</option>
                                    </select>
                                    <p class="text-muted"><small>{{ fs_t($L, 'setting_enable_help', 'Globally enable or disable Free Servers for all users.') }}</small></p>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">{{ fs_t($L, 'setting_max_servers', 'Max Servers per User') }}</label>
                                <div>
                                    <input type="number" name="max_servers_per_user" class="form-control" style="width:110px;" value="{{ $settings->max_servers_per_user ?? 1 }}" min="0" max="100">
                                    <p class="text-muted"><small>{{ fs_t($L, 'setting_max_servers_help', 'Global default — can be overridden per user.') }}</small></p>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">{{ fs_t($L, 'setting_enable_stats', 'Statistics Dashboard') }}</label>
                                <div>
                                    <select name="enable_stats" class="form-control" style="width:180px;">
                                        <option value="1" {{ ($settings->enable_stats ?? 1) == 1 ? 'selected' : '' }}>✓ {{ fs_t($L, 'setting_enabled', 'Enabled') }}</option>
                                        <option value="0" {{ ($settings->enable_stats ?? 1) == 0 ? 'selected' : '' }}>✗ {{ fs_t($L, 'setting_disabled', 'Disabled') }}</option>
                                    </select>
                                    <p class="text-muted"><small>{{ fs_t($L, 'setting_enable_stats_help', 'Show statistics at the top of this admin page.') }}</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">{{ fs_t($L, 'setting_language', 'Language') }}</label>
                                <div>
                                    <select name="language" class="form-control">
                                        <option value="en" {{ ($settings->language ?? 'en') == 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                                        <option value="de" {{ ($settings->language ?? 'en') == 'de' ? 'selected' : '' }}>🇩🇪 Deutsch</option>
                                        <option value="fr" {{ ($settings->language ?? 'en') == 'fr' ? 'selected' : '' }}>🇫🇷 Français</option>
                                        <option value="es" {{ ($settings->language ?? 'en') == 'es' ? 'selected' : '' }}>🇪🇸 Español</option>
                                        <option value="it" {{ ($settings->language ?? 'en') == 'it' ? 'selected' : '' }}>🇮🇹 Italiano</option>
                                        <option value="pt" {{ ($settings->language ?? 'en') == 'pt' ? 'selected' : '' }}>🇵🇹 Português</option>
                                        <option value="nl" {{ ($settings->language ?? 'en') == 'nl' ? 'selected' : '' }}>🇳🇱 Nederlands</option>
                                        <option value="pl" {{ ($settings->language ?? 'en') == 'pl' ? 'selected' : '' }}>🇵🇱 Polski</option>
                                        <option value="cs" {{ ($settings->language ?? 'en') == 'cs' ? 'selected' : '' }}>🇨🇿 Čeština</option>
                                        <option value="ro" {{ ($settings->language ?? 'en') == 'ro' ? 'selected' : '' }}>🇷🇴 Română</option>
                                        <option value="sv" {{ ($settings->language ?? 'en') == 'sv' ? 'selected' : '' }}>🇸🇪 Svenska</option>
                                        <option value="hu" {{ ($settings->language ?? 'en') == 'hu' ? 'selected' : '' }}>🇭🇺 Magyar</option>
                                        <option value="el" {{ ($settings->language ?? 'en') == 'el' ? 'selected' : '' }}>🇬🇷 Ελληνικά</option>
                                        <option value="da" {{ ($settings->language ?? 'en') == 'da' ? 'selected' : '' }}>🇩🇰 Dansk</option>
                                        <option value="fi" {{ ($settings->language ?? 'en') == 'fi' ? 'selected' : '' }}>🇫🇮 Suomi</option>
                                        <option value="no" {{ ($settings->language ?? 'en') == 'no' ? 'selected' : '' }}>🇳🇴 Norsk</option>
                                        <option value="tr" {{ ($settings->language ?? 'en') == 'tr' ? 'selected' : '' }}>🇹🇷 Türkçe</option>
                                    </select>
                                    <p class="text-muted"><small>{{ fs_t($L, 'setting_language_help', 'User-facing language. Applies to all users (17 languages).') }}</small></p>
                                </div>
                            </div>
                            <div class="form-group col-md-8">
                                <label class="control-label">{{ fs_t($L, 'setting_description', 'Server Description') }}</label>
                                <div>
                                    <input type="text" name="server_description" class="form-control"
                                           value="{{ $settings->server_description ?? '' }}"
                                           placeholder="{{ fs_t($L, 'setting_description_placeholder', 'Free Server created via Free Servers Extension') }}">
                                    <p class="text-muted"><small>{{ fs_t($L, 'setting_description_help', 'This description is applied to every newly created free server.') }}</small></p>
                                </div>
                            </div>
                        </div>
                    </div>{{-- /tab-general --}}

                    {{-- TAB: RESOURCES --}}
                    <div class="tab-pane" id="tab-resources">
                        <p class="text-muted">{{ fs_t($L, 'resources_help', 'Default resource allocation for all free servers. Can be overridden per egg.') }}</p>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label class="control-label"><i class="fa fa-memory"></i> {{ fs_t($L, 'resource_ram', 'RAM (MB)') }}</label>
                                <input type="number" name="default_memory" class="form-control" value="{{ $settings->default_memory ?? 1024 }}" min="128">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label"><i class="fa fa-hdd-o"></i> {{ fs_t($L, 'resource_disk', 'Disk (MB)') }}</label>
                                <input type="number" name="default_disk" class="form-control" value="{{ $settings->default_disk ?? 5120 }}" min="256">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label"><i class="fa fa-microchip"></i> {{ fs_t($L, 'resource_cpu', 'CPU (%)') }}</label>
                                <input type="number" name="default_cpu" class="form-control" value="{{ $settings->default_cpu ?? 100 }}" min="1" max="1000">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label"><i class="fa fa-eye"></i> {{ fs_t($L, 'setting_display_unit', 'Display Unit') }}</label>
                                <select name="display_unit" class="form-control">
                                    <option value="MB" {{ ($settings->display_unit ?? 'MB') == 'MB' ? 'selected' : '' }}>{{ fs_t($L, 'setting_display_unit_mb', 'MB (Megabyte)') }}</option>
                                    <option value="GB" {{ ($settings->display_unit ?? 'MB') == 'GB' ? 'selected' : '' }}>{{ fs_t($L, 'setting_display_unit_gb', 'GB (Gigabyte)') }}</option>
                                </select>
                                <p class="text-muted"><small>{{ fs_t($L, 'setting_display_unit_help', 'How RAM and disk are shown to users.') }}</small></p>
                            </div>
                        </div>
                        <hr>
                        <h4>{{ fs_t($L, 'section_advanced_resources', 'Advanced') }}</h4>
                        <div class="row">
                            <div class="form-group col-md-2">
                                <label class="control-label">{{ fs_t($L, 'resource_swap', 'Swap (MB)') }}</label>
                                <input type="number" name="default_swap" class="form-control" value="{{ $settings->default_swap ?? 0 }}" min="0">
                                <p class="text-muted"><small>0 = disabled</small></p>
                            </div>
                            <div class="form-group col-md-2">
                                <label class="control-label">{{ fs_t($L, 'resource_io', 'Block IO') }}</label>
                                <input type="number" name="default_io" class="form-control" value="{{ $settings->default_io ?? 500 }}" min="10" max="1000">
                                <p class="text-muted"><small>10–1000</small></p>
                            </div>
                            <div class="form-group col-md-2">
                                <label class="control-label">{{ fs_t($L, 'resource_databases', 'Databases') }}</label>
                                <input type="number" name="default_databases" class="form-control" value="{{ $settings->default_databases ?? 0 }}" min="0">
                            </div>
                            <div class="form-group col-md-2">
                                <label class="control-label">{{ fs_t($L, 'resource_ports', 'Ports') }}</label>
                                <input type="number" name="default_allocations" class="form-control" value="{{ $settings->default_allocations ?? 1 }}" min="1">
                            </div>
                            <div class="form-group col-md-2">
                                <label class="control-label">{{ fs_t($L, 'resource_backups', 'Backups') }}</label>
                                <input type="number" name="default_backups" class="form-control" value="{{ $settings->default_backups ?? 0 }}" min="0">
                            </div>
                        </div>
                    </div>{{-- /tab-resources --}}

                    {{-- TAB: EXPIRATION --}}
                    <div class="tab-pane" id="tab-expiration">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="callout callout-info">
                                    <p>{{ fs_t($L, 'expiration_info', 'Set to 0 to disable expiration entirely — servers will never expire.') }}</p>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">{{ fs_t($L, 'setting_expiration_days', 'Expiration Days') }}</label>
                                    <div class="input-group" style="width:160px;">
                                        <input type="number" name="expiration_days" class="form-control" value="{{ $settings->expiration_days ?? 0 }}" min="0" max="365">
                                        <span class="input-group-addon">{{ fs_t($L, 'days', 'days') }}</span>
                                    </div>
                                    <p class="text-muted"><small>{{ fs_t($L, 'setting_expiration_days_help', '0 = never expires') }}</small></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ fs_t($L, 'setting_allow_extension', 'Allow User Extensions') }}</label>
                                    <div>
                                        <select name="allow_extension" class="form-control" style="width:180px;">
                                            <option value="1" {{ ($settings->allow_extension ?? 1) == 1 ? 'selected' : '' }}>✓ {{ fs_t($L, 'yes', 'Yes') }}</option>
                                            <option value="0" {{ ($settings->allow_extension ?? 1) == 0 ? 'selected' : '' }}>✗ {{ fs_t($L, 'no', 'No') }}</option>
                                        </select>
                                        <p class="text-muted"><small>{{ fs_t($L, 'setting_allow_extension_help', 'Users can extend their server\'s expiration date.') }}</small></p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">{{ fs_t($L, 'setting_extension_days', 'Days per Extension') }}</label>
                                    <div class="input-group" style="width:160px;">
                                        <input type="number" name="extension_days" class="form-control" value="{{ $settings->extension_days ?? 30 }}" min="1" max="180">
                                        <span class="input-group-addon">{{ fs_t($L, 'days', 'days') }}</span>
                                    </div>
                                    <p class="text-muted"><small>{{ fs_t($L, 'setting_extension_days_help', 'Days added each time a user extends.') }}</small></p>
                                </div>
                            </div>
                        </div>
                    </div>{{-- /tab-expiration --}}

                    {{-- TAB: DISCORD --}}
                    <div class="tab-pane" id="tab-discord">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label class="control-label">{{ fs_t($L, 'setting_discord_webhook', 'Webhook URL') }}</label>
                                    <input type="url" name="discord_webhook_url" class="form-control"
                                           value="{{ $settings->discord_webhook_url ?? '' }}"
                                           placeholder="https://discord.com/api/webhooks/...">
                                    <p class="text-muted"><small>{{ fs_t($L, 'setting_discord_webhook_help', 'Leave empty to disable Discord notifications.') }}</small></p>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="control-label">{{ fs_t($L, 'discord_events', 'Notification Events') }}</label>
                                <table class="table table-condensed" style="margin-bottom:0;">
                                    <tbody>
                                        <tr>
                                            <td><i class="fa fa-plus-circle text-green"></i> {{ fs_t($L, 'setting_discord_create', 'Server Created') }}</td>
                                            <td style="width:130px;">
                                                <select name="discord_notify_create" class="form-control input-sm">
                                                    <option value="1" {{ ($settings->discord_notify_create ?? 0) == 1 ? 'selected' : '' }}>✓ {{ fs_t($L, 'yes', 'Yes') }}</option>
                                                    <option value="0" {{ ($settings->discord_notify_create ?? 0) == 0 ? 'selected' : '' }}>✗ {{ fs_t($L, 'no', 'No') }}</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fa fa-trash text-red"></i> {{ fs_t($L, 'setting_discord_delete', 'Server Deleted') }}</td>
                                            <td>
                                                <select name="discord_notify_delete" class="form-control input-sm">
                                                    <option value="1" {{ ($settings->discord_notify_delete ?? 0) == 1 ? 'selected' : '' }}>✓ {{ fs_t($L, 'yes', 'Yes') }}</option>
                                                    <option value="0" {{ ($settings->discord_notify_delete ?? 0) == 0 ? 'selected' : '' }}>✗ {{ fs_t($L, 'no', 'No') }}</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fa fa-clock-o text-yellow"></i> {{ fs_t($L, 'setting_discord_expire', 'Server Expired') }}</td>
                                            <td>
                                                <select name="discord_notify_expire" class="form-control input-sm">
                                                    <option value="1" {{ ($settings->discord_notify_expire ?? 0) == 1 ? 'selected' : '' }}>✓ {{ fs_t($L, 'yes', 'Yes') }}</option>
                                                    <option value="0" {{ ($settings->discord_notify_expire ?? 0) == 0 ? 'selected' : '' }}>✗ {{ fs_t($L, 'no', 'No') }}</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>{{-- /tab-discord --}}

                    {{-- TAB: NODES --}}
                    <div class="tab-pane" id="tab-nodes">
                        <div class="callout callout-info">
                            <p>{{ fs_t($L, 'nodes_help', 'If no nodes are selected, all nodes are allowed.') }}</p>
                        </div>
                        <div class="row">
                            @forelse($nodes as $node)
                            <div class="col-md-3 col-sm-4 col-xs-6">
                                <div class="node-check">
                                    <label style="color:#cad1d8; font-weight:normal; cursor:pointer;">
                                        <input type="checkbox"
                                               name="allowed_nodes[]"
                                               value="{{ $node->id }}"
                                               style="margin-right:6px; cursor:pointer; vertical-align:middle;"
                                               {{ in_array(strval($node->id), $allowedNodeIds) ? 'checked' : '' }}>
                                        <i class="fa fa-server" style="color:#8fa3b1;"></i> {{ $node->name }}
                                    </label>
                                </div>
                            </div>
                            @empty
                            <div class="col-xs-12">
                                <p style="color:#9ab0be;"><i class="fa fa-info-circle"></i> {{ fs_t($L, 'nodes_none', 'No nodes available.') }}</p>
                            </div>
                            @endforelse
                        </div>
                    </div>{{-- /tab-nodes --}}

                </div>{{-- /tab-content --}}

                {{-- Save button — always visible, outside tab-content --}}
                <div class="fs-tab-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> {{ fs_t($L, 'btn_save', 'Save Settings') }}
                    </button>
                </div>

            </div>{{-- /nav-tabs-custom --}}
        </form>
    </div>
</div>

{{-- ===== EGGS ===== --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-puzzle-piece"></i> {{ fs_t($L, 'section_eggs', 'Allowed Eggs') }}</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ $root }}" class="form-inline fs-well">
                    @csrf
                    <input type="hidden" name="action" value="add_egg">
                    <div class="form-group">
                        <select name="egg_id" class="form-control" required style="width:200px;">
                            <option value="">{{ fs_t($L, 'egg_select', '-- Select Egg --') }}</option>
                            @foreach($nests as $nest)
                                <optgroup label="{{ $nest->name }}">
                                    @foreach($nest->eggs as $egg)
                                        <option value="{{ $egg->id }}">{{ $egg->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    &nbsp;
                    <div class="form-group">
                        <input type="text" name="custom_name" class="form-control" placeholder="{{ fs_t($L, 'egg_custom_name', 'Custom Name') }}" style="width:140px;">
                    </div>
                    &nbsp;
                    <div class="form-group">
                        <div class="input-group" style="width:105px;">
                            <input type="number" name="custom_memory" class="form-control" placeholder="RAM">
                            <span class="input-group-addon">MB</span>
                        </div>
                    </div>
                    &nbsp;
                    <div class="form-group">
                        <div class="input-group" style="width:105px;">
                            <input type="number" name="custom_disk" class="form-control" placeholder="Disk">
                            <span class="input-group-addon">MB</span>
                        </div>
                    </div>
                    &nbsp;
                    <div class="form-group">
                        <div class="input-group" style="width:85px;">
                            <input type="number" name="custom_cpu" class="form-control" placeholder="CPU">
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>
                    &nbsp;
                    <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> {{ fs_t($L, 'btn_add', 'Add') }}</button>
                </form>

                @if(isset($allowedEggs) && $allowedEggs->count() > 0)
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>{{ fs_t($L, 'table_name', 'Name') }}</th>
                            <th>{{ fs_t($L, 'table_nest', 'Egg') }}</th>
                            <th style="width:95px;">RAM</th>
                            <th style="width:95px;">Disk</th>
                            <th style="width:75px;">CPU</th>
                            <th style="width:90px;">{{ fs_t($L, 'table_status', 'Status') }}</th>
                            <th style="width:80px;">{{ fs_t($L, 'table_actions', 'Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allowedEggs as $egg)
                        <tr>
                            <td><strong>{{ $egg->custom_name ?? $egg->egg_name ?? 'Unknown' }}</strong></td>
                            <td class="text-muted">{{ $egg->egg_name ?? '-' }}</td>
                            <td>{{ $egg->custom_memory ?? $settings->default_memory }} MB</td>
                            <td>{{ $egg->custom_disk ?? $settings->default_disk }} MB</td>
                            <td>{{ $egg->custom_cpu ?? $settings->default_cpu }}%</td>
                            <td>
                                @if($egg->enabled == 1)
                                    <span class="label label-success">{{ fs_t($L, 'egg_status_active', 'Active') }}</span>
                                @else
                                    <span class="label label-warning">{{ fs_t($L, 'egg_status_disabled', 'Disabled') }}</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ $root }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="action" value="toggle_egg">
                                    <input type="hidden" name="egg_id" value="{{ $egg->id }}">
                                    <button type="submit" class="btn btn-xs {{ $egg->enabled == 1 ? 'btn-warning' : 'btn-success' }}">
                                        <i class="fa fa-{{ $egg->enabled == 1 ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ $root }}" style="display:inline;" onsubmit="return confirm('{{ fs_t($L, 'eggs_confirm_remove', 'Really remove this egg?') }}')">
                                    @csrf
                                    <input type="hidden" name="action" value="remove_egg">
                                    <input type="hidden" name="egg_id" value="{{ $egg->id }}">
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="callout callout-info">
                    <p><i class="fa fa-info-circle"></i> {{ fs_t($L, 'eggs_none', 'No eggs added yet.') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== PER-USER LIMITS ===== --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users"></i> {{ fs_t($L, 'section_user_limits', 'Per-User Limits') }}</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body">
                <p class="text-muted">{!! sprintf(fs_t($L, 'user_limits_help', 'Custom limits per user. Without a custom limit, users use the global limit (%s).'), $settings->max_servers_per_user ?? 1) !!}</p>

                <div class="fs-well">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group" style="position:relative; margin-bottom:0;">
                                <label class="control-label">{{ fs_t($L, 'user_search', 'Search User') }}</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input type="text" id="user-search" class="form-control" placeholder="{{ fs_t($L, 'user_search_placeholder', 'Search by username or email...') }}" autocomplete="off">
                                </div>
                                <div id="user-search-results" style="position:absolute; top:100%; left:0; right:0; z-index:1000; border-top:none; border-radius:0 0 4px 4px; max-height:250px; overflow-y:auto; display:none; box-shadow:0 4px 8px rgba(0,0,0,0.3);"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="control-label">{{ fs_t($L, 'user_selected', 'Selected User') }}</label>
                                <input type="text" id="selected-user-display" class="form-control" readonly placeholder="{{ fs_t($L, 'user_no_selected', 'No user selected') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="control-label">{{ fs_t($L, 'user_max_servers', 'Max Servers') }}</label>
                                <input type="number" id="new-user-limit" class="form-control" value="1" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="control-label">&nbsp;</label>
                                <form method="POST" action="{{ $root }}" id="add-user-limit-form" style="display:block;">
                                    @csrf
                                    <input type="hidden" name="action" value="add_user_limit">
                                    <input type="hidden" name="user_id" id="selected-user-id" value="">
                                    <input type="hidden" name="max_servers" id="selected-max-servers" value="1">
                                    <button type="submit" class="btn btn-sm btn-success btn-block" id="add-limit-btn" disabled>
                                        <i class="fa fa-plus"></i> {{ fs_t($L, 'user_add_limit', 'Add') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($userLimits) && $userLimits->count() > 0)
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>{{ fs_t($L, 'table_username', 'Username') }}</th>
                            <th>{{ fs_t($L, 'table_email', 'Email') }}</th>
                            <th style="width:150px;">{{ fs_t($L, 'table_max_servers', 'Max Servers') }}</th>
                            <th style="width:85px;">{{ fs_t($L, 'table_current', 'Current') }}</th>
                            <th style="width:105px;">{{ fs_t($L, 'table_status', 'Status') }}</th>
                            <th style="width:105px;">{{ fs_t($L, 'table_actions', 'Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userLimits as $limit)
                        <tr>
                            <td><strong>{{ $limit->username }}</strong></td>
                            <td class="text-muted">{{ $limit->email }}</td>
                            <td>
                                <form method="POST" action="{{ $root }}" style="display:flex; gap:4px;">
                                    @csrf
                                    <input type="hidden" name="action" value="update_user_limit">
                                    <input type="hidden" name="limit_id" value="{{ $limit->id }}">
                                    <input type="number" name="max_servers" class="form-control input-sm" value="{{ $limit->max_servers }}" min="0" max="100" style="width:65px;">
                                    <button type="submit" class="btn btn-xs btn-primary"><i class="fa fa-check"></i></button>
                                </form>
                            </td>
                            <td><span class="badge">{{ $limit->current_servers }}</span></td>
                            <td>
                                @if($limit->max_servers == 0)
                                    <span class="label label-danger">{{ fs_t($L, 'status_blocked', 'Blocked') }}</span>
                                @elseif($limit->current_servers >= $limit->max_servers)
                                    <span class="label label-warning">{{ fs_t($L, 'status_at_limit', 'At Limit') }}</span>
                                @else
                                    <span class="label label-success">{{ fs_t($L, 'status_can_create', 'Can Create') }}</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ $root }}" onsubmit="return confirm('{{ sprintf(fs_t($L, 'user_limit_confirm_remove', 'Remove custom limit for %s?'), $limit->username) }}')">
                                    @csrf
                                    <input type="hidden" name="action" value="remove_user_limit">
                                    <input type="hidden" name="limit_id" value="{{ $limit->id }}">
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> {{ fs_t($L, 'btn_remove', 'Remove') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="callout callout-info">
                    <p>{!! sprintf(fs_t($L, 'user_limits_none', 'No custom limits set. All users use the global limit of <strong>%s</strong>.'), $settings->max_servers_per_user ?? 1) !!}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

</div>{{-- /fs-wrap --}}

<script>
var fsLang = {
    noUsersFound: '{{ addslashes(fs_t($L, 'user_search_no_results', 'No users found')) }}',
    searchError:  '{{ addslashes(fs_t($L, 'user_search_error', 'Error searching users')) }}',
    selectFirst:  '{{ addslashes(fs_t($L, 'user_select_first', 'Please select a user first!')) }}',
    hasLimit:     '{{ addslashes(fs_t($L, 'user_has_limit', 'Has Limit')) }}',
    currentServers: '{{ addslashes(fs_t($L, 'table_current', 'Current')) }}'
};
document.addEventListener('DOMContentLoaded', function() {
    var searchInput  = document.getElementById('user-search');
    var searchResults= document.getElementById('user-search-results');
    var selDisplay   = document.getElementById('selected-user-display');
    var selId        = document.getElementById('selected-user-id');
    var selMax       = document.getElementById('selected-max-servers');
    var limitInput   = document.getElementById('new-user-limit');
    var addBtn       = document.getElementById('add-limit-btn');
    var addForm      = document.getElementById('add-user-limit-form');
    var timer;

    limitInput.addEventListener('input', function() { selMax.value = this.value; });

    function buildResult(u) {
        var d = document.createElement('div');
        d.className = 'user-result';
        d.dataset.id = u.id; d.dataset.username = u.username; d.dataset.email = u.email;
        d.style.cssText = 'padding:8px 12px; border-bottom:1px solid #3d4d5c; cursor:' + (u.has_custom_limit ? 'not-allowed; opacity:.5;' : 'pointer;');
        if (u.has_custom_limit) d.dataset.disabled = 'true';
        d.innerHTML = '<strong style="color:#cad1d8;">' + u.username + '</strong>' +
            (u.has_custom_limit ? ' <span class="label label-warning">' + fsLang.hasLimit + '</span>' : '') +
            '<br><small style="color:#6d8492;">' + u.email + '</small>' +
            '<br><small style="color:#3c8dbc;"><i class="fa fa-server"></i> ' + u.current_servers + ' ' + fsLang.currentServers + '</small>';
        return d;
    }

    searchInput.addEventListener('input', function() {
        var q = this.value.trim();
        clearTimeout(timer);
        if (q.length < 2) { searchResults.style.display = 'none'; return; }
        timer = setTimeout(function() {
            fetch('{{ $root }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: 'action=search_users&query=' + encodeURIComponent(q)
            }).then(function(r){return r.json();}).then(function(users) {
                searchResults.innerHTML = '';
                if (!users.length) {
                    searchResults.innerHTML = '<div class="fs-no-results">' + fsLang.noUsersFound + '</div>';
                } else { users.forEach(function(u){ searchResults.appendChild(buildResult(u)); }); }
                searchResults.style.display = 'block';
            }).catch(function() {
                searchResults.innerHTML = '<div class="fs-no-results" style="color:#e74c3c;">' + fsLang.searchError + '</div>';
                searchResults.style.display = 'block';
            });
        }, 300);
    });

    searchResults.addEventListener('click', function(e) {
        var r = e.target.closest('.user-result');
        if (r && !r.dataset.disabled) {
            selId.value = r.dataset.id;
            selDisplay.value = r.dataset.username + ' (' + r.dataset.email + ')';
            addBtn.disabled = false;
            searchResults.style.display = 'none';
            searchInput.value = '';
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target))
            searchResults.style.display = 'none';
    });

    addForm.addEventListener('submit', function(e) {
        if (!selId.value) { e.preventDefault(); alert(fsLang.selectFirst); }
    });
});
</script>

@else
<div class="alert alert-warning">
    <i class="fa fa-exclamation-triangle"></i> {{ $lang['alert_settings_error'] ?? 'Settings could not be loaded. Please reload the page.' }}
</div>
@endif

@endsection
@endsection
