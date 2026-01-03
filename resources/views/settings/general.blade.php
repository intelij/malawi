@extends('layouts.app')

@section('page-title', __('General Settings'))
@section('page-heading', __('General Settings'))

@section('breadcrumbs')
    <li class="breadcrumb-item text-muted">
        @lang('Settings')
    </li>
    <li class="breadcrumb-item active">
        @lang('General')
    </li>
@stop

@section('content')

@include('partials.messages')

<div class="row">
    <div class="col-md-6">

        <form action="{{ route('settings.general.update') }}" method="POST" id="general-settings-form">
        @csrf

        <div class="card">
            <h6 class="card-header">
                @lang('Default')
            </h6>
            <div class="card-body">
                <div class="form-group">
                    <label for="name">@lang('Name')</label>
                    <input type="text" class="form-control input-solid" id="app_name"
                           name="app_name" value="{{ setting('app_name') }}">
                </div>

                <div class="form-group my-4">
                    <label for="default_fundraiser_length">
                        @lang('Default Fundraiser Length') <br>
                        <small class="text-muted">
                            @lang('Set the number of days the fundraiser will run for.')
                        </small>
                    </label>
                    <input type="text" name="default_fundraiser_length"
                        class="form-control input-solid" value="{{ number_format((float) setting('default_fundraiser_length', 3), 0, '.', '') }}">
                </div>

                <button type="submit" class="btn btn-primary">
                    @lang('Update')
                </button>

            </div>
        </div>

        </form>


         <div class="card">
            <h6 class="card-header">
                @lang('Bulk Users Import')
            </h6>
            <div class="card-body">

                <form action="{{ route('upload.excel') }}" method="POST"  enctype="multipart/form-data" id="fundraiser-create-form">
                    @csrf
                    <div class="form-group">
                        <label for="image">
                            @lang('Upload Excel Document') <i class="fas fa-file-excel"></i><br>
                            <small class="text-muted">
                                @lang('Upload an excel document containing users you want to import (Max size: 2MB)')
                            </small>

                        </label>
                        <input type="file"
                            name="excel_file"
                            id="excel_file"
                            class="form-control-file form-control input-solid">
                    </div>


                <button type="submit" class="btn btn-primary">
                    @lang('Upload Bulk Users Excel Spreedsheet')
                </button>

                </form>

            </div>
        </div>

    </div>

    <div class="col-md-6">



    <div class="card">
        <h6 class="card-header">
            @lang('Subscription')
        </h6>

        <div class="card-body">

            <form action="{{ route('settings.auth.update') }}" method="POST" id="auth-general-settings-form">
            @csrf

            <div class="form-group mb-4">
                <div class="d-flex align-items-center">
                    <div class="switch">
                        <input type="hidden" value="0" name="admin_set_default_amount">
                        <input type="checkbox" name="admin_set_default_amount" value="1" id="switch-remember-me" class="switch" {{ setting('admin_set_default_amount') ? 'checked' : '' }}>
                        <label for="switch-remember-me"></label>
                    </div>
                    <div class="ml-3 d-flex flex-column">
                        <label class="mb-0">@lang('Allow Admin To Set The Amount')</label>
                        <small class="pt-0 text-muted">
                            @lang("The Admin is activated to detect the amount a user is to contribute")
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-group my-4">
                <label for="default_contribution">
                    @lang('Default Contribution Amount') <br>
                    <small class="text-muted">
                        @lang('Set subscription amount that the user is expected to contribute towards the bereavement.')
                    </small>
                </label>
                <input type="text" name="default_contribution"
                    class="form-control input-solid" value="{{ number_format((float) setting('default_contribution', 6), 2, '.', '') }}">
            </div>

            <div class="form-group my-4">
                <label for="cooling_off_period">
                    @lang('Default Cooling Off Period') <br>
                    <small class="text-muted">
                        @lang('Set the default cooling off period (in months) before a member can make a claim.')
                    </small>
                </label>
                <input type="text" name="cooling_off_period"
                    class="form-control input-solid" value="{{ setting('cooling_off_period', 3) }}">
            </div>

            <div class="form-group my-4">
                <label for="cooling_off_period">
                    @lang('Default Stale Accounts Cut Off Period') <br>
                    <small class="text-muted">
                        @lang('Set the default stale accounts cut off period (in months) to see none contributing members.')
                    </small>
                </label>
                <input type="text" name="stale_cutoff_period"
                    class="form-control input-solid" value="{{ setting('stale_cutoff_period', 3) }}">
            </div>

            <button type="submit" class="btn btn-primary">
                @lang('Update')
            </button>

            </form>

        </div>
    </div>

</div>

@stop
