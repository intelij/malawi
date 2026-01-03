<?php
    // $role = \Vanguard\Role::with('users')->where('name', 'Admin')->first();

    // $is_admin = $role->users->filter(function ($object) {
    //     return $object->id === auth()->user()->id;
    // });
?>
@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">@lang('Users')</a>
    </li>
    <li class="breadcrumb-item active">
        Fundraiser
    </li>
@stop

@section('content')

 <?php

    $role = \App\Models\Role::with('users')->where('name', 'Admin')->first();

    $is_admin = $role->users->filter(function ($object) {
        return $object->id === auth()->user()->id;
    });

?>

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
                            <th class="min-width-80">@lang('Member Number')</th>
                            <th class="min-width-150">@lang('Member Name')</th>
                            <th class="min-width-100">@lang('Start Date')</th>
                            <th class="min-width-100">@lang('End Date')</th>
                            <th class="min-width-100">@lang('Amount')</th>
                            <th class="min-width-100">@lang('Export Paid')</th>
                        </tr>
                        </thead>
                        <tbody>
                            @if (count($fundraiser))
                                @foreach ($fundraiser as $fundraise)
                                    @include('user.partials.fundraiser')
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7"><em>@lang('No records found.')</em></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>
@stop
