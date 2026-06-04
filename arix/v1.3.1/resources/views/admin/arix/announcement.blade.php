@extends('layouts.arix', ['navbar' => 'announcement', 'sideEditor' => true])

@section('title')
    Arix Announcements
@endsection

@section('content')

    <form action="{{ route('admin.arix.announcement') }}" method="POST">
        <div class="header">
            <p>Announcement settings</p>
            <span class="description-text">Change the announcement settings of Arix Theme.</span>
        </div>
        <div class="input-field">
            <label for="arix:announcementType">Select announcement type</label>
            <select name="arix:announcementType" value="{{ old('arix:announcementType', $announcementType) }}">
                @if(old('arix:announcementType', $announcementType) == 'party') <option value="party">Party</option> @endif
                <option value="disabled">Disabled</option>
                <option value="update" @if(old('arix:announcementType', $announcementType) == 'update') selected @endif>Update</option>
                <option value="info" @if(old('arix:announcementType', $announcementType) == 'info') selected @endif>Info</option>
                <option value="success" @if(old('arix:announcementType', $announcementType) == 'success') selected @endif>Success</option>
                <option value="alert" @if(old('arix:announcementType', $announcementType) == 'alert') selected @endif>Alert</option>
                <option value="warning" @if(old('arix:announcementType', $announcementType) == 'warning') selected @endif>Warning</option>
            </select>
            <small>Set announcement type disabled to disable announcements.</small>
        </div>
        <div class="input-field">
            <label for="arix:announcementCloseable">Closable announcement</label>
            <select name="arix:announcementCloseable" value="{{ old('arix:announcementCloseable', $announcementCloseable) }}">
                <option value="true">Enable</option>
                <option value="false" @if(old('arix:announcementCloseable', $announcementCloseable) == 'false') selected @endif>Disable</option>
            </select>
        </div>
        <div class="input-field">
            <label for="arix:announcementMessage">Announcement message</label>
            <textarea type="text" id="arix:announcementMessage" name="arix:announcementMessage" width="100%" rows="5">{{ old('arix:announcementMessage', $announcementMessage) }}</textarea>
            <small>For styling use BBCode format.</small>
        </div>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection