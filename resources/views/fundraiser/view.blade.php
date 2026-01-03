@extends('layouts.app')

@section('page-title', '$user->present()->nameOrEmail')
@section('page-heading', '$user->present()->nameOrEmail')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">@lang('Users')</a>
    </li>
    <li class="breadcrumb-item active">
        {{-- {{ $user->present()->nameOrEmail }} --}}
    </li>
@stop

@section('content')

<div class="row">

    <div class="col-lg-12 col-xl-12">
        <div class="card">
            <h6 class="card-header d-flex align-items-center justify-content-between">
                @lang('Payment History')
            </h6>
            <div class="card-body">

                <div class="table-responsive" id="users-table-wrapper">
                    <table class="table table-borderless table-striped">
                        <thead>
                        <tr>
                            <th class="min-width-80">@lang('Date')</th>
                            <th class="min-width-150">@lang('Amount')</th>
                            <th class="min-width-100">@lang('Type')</th>
                            <th class="min-width-100">@lang('Reference')</th>
                            <th class="min-width-80">@lang('Authorised By')</th>
                            <th class="min-width-80">@lang('Status')</th>
                            <th class="text-center min-width-150">@lang('Action')</th>
                        </tr>
                        </thead>
                        <tbody>
                            {{-- @if (count($payments))
                                @foreach ($payments as $payment)
                                    @include('user.partials.payments')
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7"><em>@lang('No records found.')</em></td>
                                </tr>
                            @endif --}}
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>
@stop
