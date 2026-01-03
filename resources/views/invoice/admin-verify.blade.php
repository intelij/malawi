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

{!! Form::open(['route' => 'verify-invoice', 'id' => 'fundraiser-create-form', 'enctype' => 'multipart/form-data']) !!}

@if (count($payments)!== 0)
    @include('invoice.partial.show')
@else
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <h6 class="card-header">
                @lang('Invoice Verification')
            </h6>
            <div class="card-body">
                <div class="form-group">
                    <label for="bereaved_ref_number">
                        @lang('There are no pending invoices that need to be verified') <br>
                        <small class="text-muted">
                            @lang('Seems you have caught up with all the invoices.')
                        </small>
                    </label>
                </div>
                <table class="table table-borderless table-striped">
                    <tbody>
                        {{-- @if (count($fundraiser))
                            @foreach ($fundraiser as $fundraise)
                                @include('user.partials.fundraiser')
                            @endforeach
                        @else --}}
                            <tr>
                                <td colspan="7"><em>@lang('No records found.')</em></td>
                            </tr>
                        {{-- @endif --}}
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <div class="form-group">

                </div>
            </div>
        </div>
    </div>
</div>
@endif

@stop
