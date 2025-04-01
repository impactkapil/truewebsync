@extends('layouts.customer')

@section('customer-content')
<div class="container">
    <h2>Settings</h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Feature Name</th>
                    <th>Status</th>
                    <th>Toggle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($settings as $setting)
                    <tr>
                        <td>{{ $setting->feature_name }}</td>
                        <td>
                            @if($setting->is_enabled)
                                <span class="badge bg-success">Enabled</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('customer.settings.toggle', $setting->id) }}" method="POST">
                                @csrf
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="switch-{{ $setting->id }}" name="is_enabled" {{ $setting->is_enabled ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label" for="switch-{{ $setting->id }}"></label>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
