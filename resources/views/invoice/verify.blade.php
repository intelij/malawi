@extends('layouts.app')

@section('page-title', __('General Invoice'))
@section('page-heading', __('General Invoice'))

@section('breadcrumbs')
    <li class="breadcrumb-item text-muted">
        @lang('Verify')
    </li>
    <li class="breadcrumb-item active">
        @lang('Invoices')
    </li>
@stop

@section('content')

@include('partials.messages')


<div class="row">
    <div class="col-md-12">
        <div class="card">
            <h6 class="card-header d-flex align-items-center justify-content-between">
                @lang('Invoices')
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
                            <th class="min-width-80">@lang('Action')</th>
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
                                <td class="align-middle">{{ $payment->reference ?: $payment->user->membership_number }}</td>
                                <td class="align-middle">
                                    @if (isset($payment->authorised_by) && $payment->authorised_by)
                                        <span class="badge badge-lg badge-success">
                                            {{ $payment->authorised_by }}
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
                                <td class="text-center align-middle">
                                    <div class="dropdown show d-inline-block">
                                        <a class="btn btn-icon"
                                           href="#" role="button" id="dropdownMenuLink"
                                           data-toggle="dropdown"
                                           aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                            <a href="{{ route('users.show', $payment->user) }}" class="dropdown-item text-gray-500">
                                                <i class="fas fa-eye mr-2"></i>
                                                @lang('View Invoice')
                                            </a>

                                            {!! Form::open(['route' => 'fundraiser.create', 'files' => true, 'id' => 'user-form']) !!}
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $payment->user->id }}"/>
                                            <input type="hidden" name="reference" value="{{ $payment->user->membership_number ?? NULL }}"/>
                                            <input type="hidden" name="full_name" value="{{ $payment->user->first_name . ' ' . $payment->user->last_name }}"/>
                                            <button type="submit" class="dropdown-item text-gray-500">
                                                <i class="fas fa-pound-sign mr-2"></i>
                                                @lang('Verify Invoice')
                                            </button>
                                            {!! Form::close() !!}
                                        </div>
                                    </div>

                                    <a href="{{ route('users.edit', $payment->user) }}"
                                       class="btn btn-icon edit"
                                       title="@lang('Edit User')"
                                       data-toggle="tooltip" data-placement="top">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="{{ route('users.destroy', $payment->user) }}"
                                       class="btn btn-icon"
                                       title="@lang('Delete User')"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       data-method="DELETE"
                                       data-confirm-title="@lang('Please Confirm')"
                                       data-confirm-text="@lang('Are you sure that you want to delete this user?')"
                                       data-confirm-delete="@lang('Yes, delete him!')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>



@stop
