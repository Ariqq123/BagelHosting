@extends('layouts.arix', ['navbar' => 'plugins', 'sideEditor' => false])

@section('title')
    Plugin Downloader
@endsection

@section('content')
    <div class="content-box">
        <div class="header">
            <p>Plugin downloader</p>
            <span class="description-text">Manage plugins users can download into a server's /plugins folder.</span>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.arix.plugins.settings') }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Marketplace settings</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Manage external provider credentials.</span>
                </div>
                <div class="col-md-8">
                    <label style="display:block;margin-bottom:8px;color:var(--gray200);">CurseForge API key</label>
                    <div class="input-field">
                        <input
                            name="curseforge_api_key"
                            type="password"
                            value=""
                            placeholder="{{ $curseforgeConfigured ? 'Configured - leave blank to keep current key' : 'Paste CurseForge API key' }}"
                            autocomplete="new-password"
                        >
                    </div>
                    <p style="font-size:1.4rem;color:var(--gray300);margin-top:8px;">
                        Status: {{ $curseforgeConfigured ? 'Configured' : 'Not configured' }}
                    </p>
                    @if($curseforgeConfigured)
                        <label style="display:flex;align-items:center;gap:8px;margin:12px 0;">
                            <input type="checkbox" name="clear_curseforge_api_key" value="1">
                            Remove saved CurseForge key
                        </label>
                    @endif
                    <button type="submit" class="btn btn-primary">Save settings</button>
                </div>
            </div>
        </form>

        <form action="{{ route('admin.arix.plugins') }}" method="POST">
            @csrf
            <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                <div class="col-md-4">
                    <p style="margin:0;font-weight:550;">Add plugin</p>
                    <span style="font-size:1.5rem;color:var(--gray300);">Create a catalog item for server users.</span>
                </div>
                <div class="col-md-8">
                    <div class="input-field"><input name="name" value="{{ old('name') }}" placeholder="Plugin name" required></div>
                    <div class="input-field"><textarea name="description" placeholder="Description">{{ old('description') }}</textarea></div>
                    <div class="input-field"><input name="download_url" value="{{ old('download_url') }}" placeholder="Download URL" required></div>
                    <div class="input-field"><input name="filename" value="{{ old('filename') }}" placeholder="example.jar" required></div>
                    <div class="input-field"><input name="icon_url" value="{{ old('icon_url') }}" placeholder="Icon URL (optional)"></div>
                    <label style="display:flex;align-items:center;gap:8px;margin:12px 0;">
                        <input type="checkbox" name="enabled" value="1" {{ old('enabled', '1') ? 'checked' : '' }}>
                        Enabled
                    </label>
                    <button type="submit" class="btn btn-primary">Add plugin</button>
                </div>
            </div>
        </form>

        <div class="header" style="margin-top:50px;">
            <p>Catalog</p>
            <span class="description-text">Enabled plugins are visible in the server Management tab.</span>
        </div>

        @forelse($plugins as $plugin)
            <form action="{{ route('admin.arix.plugins.update', $plugin) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="row" style="border-bottom:1px solid var(--gray500);padding-top:20px;padding-bottom:20px;">
                    <div class="col-md-4">
                        <p style="margin:0;font-weight:550;">{{ $plugin->name }}</p>
                        <span style="font-size:1.5rem;color:var(--gray300);">{{ $plugin->filename }}</span>
                    </div>
                    <div class="col-md-8">
                        <div class="input-field"><input name="name" value="{{ old('name', $plugin->name) }}" required></div>
                        <div class="input-field"><textarea name="description">{{ old('description', $plugin->description) }}</textarea></div>
                        <div class="input-field"><input name="download_url" value="{{ old('download_url', $plugin->download_url) }}" required></div>
                        <div class="input-field"><input name="filename" value="{{ old('filename', $plugin->filename) }}" required></div>
                        <div class="input-field"><input name="icon_url" value="{{ old('icon_url', $plugin->icon_url) }}" placeholder="Icon URL (optional)"></div>
                        <label style="display:flex;align-items:center;gap:8px;margin:12px 0;">
                            <input type="checkbox" name="enabled" value="1" {{ old('enabled', $plugin->enabled) ? 'checked' : '' }}>
                            Enabled
                        </label>
                        <div style="display:flex;gap:10px;">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </form>
            <form action="{{ route('admin.arix.plugins.delete', $plugin) }}" method="POST" style="margin-top:-58px;margin-bottom:32px;text-align:right;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        @empty
            <p style="color:var(--gray300);">No plugins configured.</p>
        @endforelse
    </div>
@endsection
