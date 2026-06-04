@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'mc-versions'])

@section('title')
    MC Versions
@endsection

@section('content-header')
    <h1>MC Versions<small>Generate managed Minecraft server eggs.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.settings') }}">Settings</a></li>
        <li class="active">MC Versions</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Managed Nest Preview</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th>Resource</th>
                                <th>Name</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                            <tr>
                                <td>Nest</td>
                                <td>{{ $preview['nest']['name'] }}</td>
                                <td><code>{{ $preview['nest']['action'] }}</code></td>
                                <td>Ready</td>
                            </tr>
                            @foreach($preview['eggs'] as $egg)
                                <tr>
                                    <td>Egg</td>
                                    <td>{{ $egg['name'] }}</td>
                                    <td><code>{{ $egg['action'] }}</code></td>
                                    <td>{{ $egg['available'] ? 'Ready' : 'Skipped' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer">
                    <form action="{{ route('admin.settings.mc-versions.sync') }}" method="POST">
                        {!! csrf_field() !!}
                        <a href="{{ route('admin.settings.mc-versions') }}" class="btn btn-sm btn-default">Refresh Preview</a>
                        <button type="submit" class="btn btn-sm btn-primary pull-right">Sync Eggs</button>
                    </form>
                    <p class="text-muted no-margin">Sync creates or updates only eggs marked as managed by the MC Versions generator.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
