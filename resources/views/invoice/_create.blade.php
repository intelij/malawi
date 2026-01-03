@extends('layouts.app')

@section('page-title', __('General Invoice'))
@section('page-heading', __('General Invoice'))

@section('breadcrumbs')
    <li class="breadcrumb-item text-muted">
        @lang('Payment')
    </li>
    <li class="breadcrumb-item active">
        @lang('Invoices')
    </li>
@stop

@section('content')

@include('partials.messages')


{!! Form::open(['route' => 'register-invoice', 'id' => 'fundraiser-create-form', 'enctype' => 'multipart/form-data']) !!}
<div class="row">
    @if (count($unpaidFundraisers))
        @include('invoice.partial.create')
    @else
        <div class="col-md-12">
            <div class="card">
                <h6 class="card-header">
                    @lang('Invoice Payment')
                </h6>
                <div class="card-body">
                    <div class="form-group">
                        <label for="bereaved_ref_number">
                            @lang('There are no pending invoices') <br>
                            <small class="text-muted">
                                @lang('Seems you do not have any pending invoices linked to your account.')
                            </small>
                        </label>
                    </div>
                    <table class="table table-borderless table-striped">
                        <tbody>
                            <tr>
                                <td colspan="7"><em>@lang('No records found.')</em></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div class="form-group">

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@stop
