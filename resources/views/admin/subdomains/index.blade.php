@extends('layouts.admin')

@section('title')
    Subdomains
@endsection

@section('content-header')
    <h1>Subdomains<small>Manage Cloudflare-backed root domains available to servers.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Subdomains</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Domain List</h3>
                <div class="box-tools">
                    <a class="btn btn-sm btn-primary" href="{{ route('admin.subdomains.create') }}">Create New</a>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>ID</th>
                            <th>Domain</th>
                            <th>Zone ID</th>
                            <th>Types</th>
                            <th class="text-center">Proxied</th>
                            <th class="text-center">Enabled</th>
                            <th class="text-center">Records</th>
                        </tr>
                        @forelse ($domains as $domain)
                            <tr>
                                <td><code>{{ $domain->id }}</code></td>
                                <td><a href="{{ route('admin.subdomains.edit', $domain->id) }}">{{ $domain->name }}</a></td>
                                <td><code>{{ $domain->cloudflare_zone_id }}</code></td>
                                <td>{{ implode(', ', $domain->allowed_record_types ?? []) }}</td>
                                <td class="text-center"><i class="fa fa-{{ $domain->proxied ? 'check text-green' : 'times text-red' }}"></i></td>
                                <td class="text-center"><i class="fa fa-{{ $domain->enabled ? 'check text-green' : 'times text-red' }}"></i></td>
                                <td class="text-center">{{ $domain->subdomains_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No subdomain domains have been configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection