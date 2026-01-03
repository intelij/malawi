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


{{-- @foreach ($fundraisers as $fundraiser)
    <p>
        {{ $fundraiser }} - {{ count($fundraisers) }}
    </p>
@endforeach --}}

{{-- @dump($latestFundraiser) --}}

{!! Form::open(['route' => 'register-invoice', 'id' => 'fundraiser-create-form', 'enctype' => 'multipart/form-data']) !!}

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <h6 class="card-header">
                @lang('Pay Outstanding Invoice')
                <small style="float: right;">
                    <a href="#"
                       data-toggle="tooltip"
                       data-placement="top"
                       title="@lang('You have outstanding payments')">
                       Paid {{ count($alreadyPaidInvoices) }} out of {{ $totalFundraisers }} invoices

                    </a>
                </small>
            </h6>
            <div class="card-body">
                <div class="form-group">
                    <label for="bereaved_ref_number">
                        @lang('Bereaved Member') <br>
                        <small class="text-muted">
                            @lang('This is the reference number of the bereaved member.')
                        </small>
                    </label>
                    <input type="text" name="bereaved_ref_number"
                        class="form-control input-solid"
                        value="{{ $latestFundraiser->user->membership_number }} - {{ $latestFundraiser->user->first_name }} {{ $latestFundraiser->user->last_name }}"
                        disabled>
                    <input type="hidden" name="reference" value="{{ $latestFundraiser->user->membership_number }}" >
                </div>

                <div class="form-group my-4">
                    <label for="name">@lang('Payment Type')</label>
                    <input type="hidden" name="payment_type" value="Bereavement Fund">
                    <input type="text" class="form-control input-solid" id="app_name"
                           name="app_name" value="Bereavement Fund" disabled>
                </div>

                <div class="form-group my-4">
                    <label for="image">Upload proof of payment as an image (Max size: 2MB)</label>
                    <input type="file"
                        name="invoice"
                        id="invoice"
                        class="form-control-file form-control input-solid">
                </div>

                <div class="form-group my-4">
                    <label for="default_contribution">
                        @lang('Contribution Amount') <br>
                        <small class="text-muted">
                            @lang('Set subscription amount that the user is expected to contribute towards the bereavement.')
                        </small>
                    </label>
                    <input type="text" name="default_contribution_display"
                        class="form-control input-solid"
                        value="{{ number_format((float) setting('default_contribution', 6), 2, '.', '') }}"
                        disabled>
                    <input type="hidden" name="amount" value="{{ number_format((float) setting('default_contribution', 6), 2, '.', '') }}" />
                </div>

                <button type="submit" class="btn btn-primary">
                    @lang('Upload Invoice For Verification')
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <h6 class="card-header d-flex align-items-center justify-content-between">
                @lang('Payment History')
            </h6>
            <div class="card-body">

                <div class="table-responsive" id="users-table-wrapper">
                    <table class="table table-borderless table-striped">
                        <thead>
                        <tr>
                            <th class="min-width-80">@lang('Proof')</th>
                            <th class="min-width-150">@lang('Amount')</th>
                            <th class="min-width-100">@lang('Type')</th>
                            <th class="min-width-100">@lang('Reference')</th>
                            <th class="min-width-80">@lang('Authorised By')</th>
                            <th class="min-width-80">@lang('Status')</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                            <tr>
                                <td class="align-middle">
                                    @if (isset($payment->payment_type) && $payment->payment_type == "Bereaved")
                                        <img src="/upload/invoices/8234.jpg" width="100"/>
                                    @else
                                        <img src="/upload/invoices/{{ $payment->image_name }}" width="100"/>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $payment->amount ?: __('N/A') }} </td>
                                <td class="align-middle">{{ $payment->payment_type ?: __('N/A') }} </td>
                                {{-- <td class="align-middle">{{ $payment->reference ?: __(' - ') }}</td> --}}
                                <td class="align-middle">{{ $payment->reference ?: $payment->user->membership_number }}</td>
                                <td class="align-middle">
                                    @if (isset($payment->authorised_by) && $payment->authorised_by)
                                        <span class="badge badge-lg badge-success">
                                            @if ($payment->authorised_by <= 9)
                                                PAM000{{ $payment->authorised_by }}
                                            @elseif ($payment->authorised_by >= 10 && $payment->authorised_by <= 99)
                                                PAM00{{ $payment->authorised_by }}
                                            @elseif ($payment->authorised_by >= 100 && $payment->authorised_by <= 999)
                                                PAM0{{ $payment->authorised_by }}
                                            @else
                                                PAM{{ $payment->authorised_by }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="badge badge-lg badge-warning">
                                            Unassigned
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if (isset($payment->verified) && $payment->verified)
                                        <span class="badge badge-lg badge-success">
                                            Verified
                                        </span>
                                    @elseif (isset($payment->payment_type) && $payment->payment_type == "Bereaved")
                                        <span class="badge badge-lg badge-success">
                                            Verified
                                        </span>
                                    @else
                                        <span class="badge badge-lg badge-warning">
                                            Pending Verification
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

</div>



@stop
