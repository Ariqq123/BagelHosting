@extends('layouts.admin')

@section('title')
    Subdomains &rarr; {{ $mode === 'create' ? 'Create' : $domain->name }}
@endsection

@section('content-header')
    <h1>{{ $mode === 'create' ? 'Create Subdomain Domain' : $domain->name }}<small>Cloudflare DNS configuration.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.subdomains') }}">Subdomains</a></li>
        <li class="active">{{ $mode === 'create' ? 'Create' : $domain->name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Domain Details</h3>
            </div>
            <form action="{{ $mode === 'create' ? route('admin.subdomains.store') : route('admin.subdomains.update', $domain->id) }}" method="POST">
                <div class="box-body">
                    <div class="form-group">
                        <label for="pName" class="control-label">Root Domain</label>
                        <input type="text" id="pName" name="name" class="form-control" value="{{ old('name', $domain->name) }}" placeholder="example.com" />
                    </div>
                    <div class="form-group">
                        <label for="pZoneId" class="control-label">Cloudflare Zone ID</label>
                        <input type="text" id="pZoneId" name="cloudflare_zone_id" class="form-control" value="{{ old('cloudflare_zone_id', $domain->cloudflare_zone_id) }}" />
                    </div>
                    <div class="form-group">
                        <label for="pToken" class="control-label">Cloudflare API Token</label>
                        <input type="password" id="pToken" name="cloudflare_token" class="form-control" value="" />
                        @if($mode === 'edit')
                            <p class="text-muted small">Leave blank to keep the existing token.</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="control-label">Allowed Record Types</label>
                        @php($types = old('allowed_record_types', $domain->allowed_record_types ?? []))
                        <div class="checkbox checkbox-primary">
                            <input id="pTypeA" type="checkbox" name="allowed_record_types[]" value="A" @if(in_array('A', $types, true)) checked @endif />
                            <label for="pTypeA">A records</label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input id="pTypeCname" type="checkbox" name="allowed_record_types[]" value="CNAME" @if(in_array('CNAME', $types, true)) checked @endif />
                            <label for="pTypeCname">CNAME records</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pCnameTarget" class="control-label">CNAME Target</label>
                        <input type="text" id="pCnameTarget" name="cname_target" class="form-control" value="{{ old('cname_target', $domain->cname_target) }}" placeholder="node.example.com" />
                    </div>
                    <div class="checkbox checkbox-primary">
                        <input id="pProxied" type="checkbox" name="proxied" value="1" @if(old('proxied', $domain->proxied)) checked @endif />
                        <label for="pProxied">Proxy records through Cloudflare</label>
                    </div>
                    <div class="checkbox checkbox-primary">
                        <input id="pEnabled" type="checkbox" name="enabled" value="1" @if(old('enabled', $domain->enabled)) checked @endif />
                        <label for="pEnabled">Enable this domain for users</label>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    @if($mode === 'edit')
                        {!! method_field('PATCH') !!}
                    @endif
                    <a href="{{ route('admin.subdomains') }}" class="btn btn-default btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm pull-right">Save</button>
                </div>
            </form>
        </div>
    </div>
    @if($mode === 'edit')
        <div class="col-sm-4">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Delete Domain</h3>
                </div>
                <div class="box-body">
                    <p>Domains can only be deleted after all subdomains using them have been removed.</p>
                </div>
                <div class="box-footer">
                    <form action="{{ route('admin.subdomains.delete', $domain->id) }}" method="POST">
                        {!! csrf_field() !!}
                        {!! method_field('DELETE') !!}
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
