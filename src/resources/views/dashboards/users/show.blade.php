@extends('dashboards.layouts.dashboards')

@section('title', __('views.dashboards.users.show.title', ['name' => $user->name]))

@section('content')
    <div class="row">
        <table class="table table-striped table-hover">
            <tbody>
            <tr>
                <th>{{ __('views.dashboards.users.show.table_header_0') }}</th>
                <td><img src="{{ $user->avatar }}" class="user-profile-image"></td>
            </tr>

            <tr>
                <th>{{ __('views.dashboards.users.show.table_header_1') }}</th>
                <td>{{ $user->name }}</td>
            </tr>

            <tr>
                <th>{{ __('views.dashboards.users.show.table_header_2') }}</th>
                <td>
                    <a href="mailto:{{ $user->email }}">
                        {{ $user->email }}
                    </a>
                </td>
            </tr>
            <tr>
                <th>{{ __('dashboards') }}</th>
                <td>
                    {{ $user->roles->pluck('name')->implode(',') }}
                </td>
            </tr>
            <tr>
                <th>{{ __('views.dashboards.users.show.table_header_4') }}</th>
                <td>
                    @if($user->active)
                        <span class="label label-primary">{{ __('views.dashboards.users.show.active') }}</span>
                    @else
                        <span class="label label-danger">{{ __('views.dashboards.users.show.inactive') }}</span>
                    @endif
                </td>
            </tr>

            <tr>
                <th>{{ __('views.dashboards.users.show.table_header_5') }}</th>
                <td>
                    @if($user->confirmed)
                        <span class="label label-success">{{ __('views.dashboards.users.show.confirmed') }}</span>
                    @else
                        <span class="label label-warning">{{ __('views.dashboards.users.show.not_confirmed') }}</span>
                    @endif</td>
                </td>
            </tr>

            <tr>
                <th>{{ __('views.dashboards.users.show.table_header_6') }}</th>
                <td>{{ $user->created_at }} ({{ $user->created_at->diffForHumans() }})</td>
            </tr>

            <tr>
                <th>{{ __('views.dashboards.users.show.table_header_7') }}</th>
                <td>{{ $user->updated_at }} ({{ $user->updated_at->diffForHumans() }})</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection