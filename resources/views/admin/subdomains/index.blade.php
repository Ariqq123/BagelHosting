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
                <h3 class="box-title">Import from Cloudflare</h3>
            </div>
            <form action="{{ route('admin.subdomains.import.preview') }}" method="POST">
                <div class="box-body">
                    <div class="form-group">
                        <label for="pImportToken" class="control-label">Cloudflare API Token</label>
                        <input type="password" id="pImportToken" name="cloudflare_token" class="form-control" value="{{ old('cloudflare_token', $importToken ?? '') }}" />
                        <p class="text-muted small">Token requires <code>Zone:Read</code> and <code>DNS:Edit</code>. Imported zones are enabled with A records; edit a domain after import to add CNAME support.</p>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-sm btn-primary">List Cloudflare Zones</button>
                </div>
            </form>
        </div>
    </div>
</div>
@if(isset($importZones))
<div class="row">
    <div class="col-xs-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Cloudflare Zones</h3>
            </div>
            <form action="{{ route('admin.subdomains.import') }}" method="POST">
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th></th>
                                <th>Domain</th>
                                <th>Zone ID</th>
                                <th>Status</th>
                            </tr>
                            @forelse($importZones as $index => $zone)
                                @php($exists = in_array($zone['name'], $existingDomainNames ?? [], true))
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="zones[{{ $index }}][selected]" value="1" @if(!$exists) checked @else disabled @endif />
                                        <input type="hidden" name="zones[{{ $index }}][id]" value="{{ $zone['id'] }}" />
                                        <input type="hidden" name="zones[{{ $index }}][name]" value="{{ $zone['name'] }}" />
                                    </td>
                                    <td>{{ $zone['name'] }}</td>
                                    <td><code>{{ $zone['id'] }}</code></td>
                                    <td>{{ $exists ? 'Already imported' : 'Ready' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No zones were returned by Cloudflare.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <input type="hidden" name="cloudflare_token" value="{{ $importToken }}" />
                    <button type="submit" class="btn btn-sm btn-success">Import Selected Zones</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
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