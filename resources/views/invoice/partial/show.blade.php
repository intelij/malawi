<div class="row">
    <div class="col-md-6">
        <div class="card">
            <h6 class="card-header">
                @lang('Invoice Verification')
            </h6>
            <div class="card-body">
                <div class="form-group">
                    <label for="bereaved_ref_number">
                        @lang('Payee Details') <br>
                        <small class="text-muted">
                            @lang('Paid member, membership reference number and name.')
                        </small>
                    </label>

                    @if (is_null($verify))
                        <input type="text" name="bereaved_ref_number"
                        class="form-control input-solid"
                        value="PLEASE SELECT PAYMENT TO VERIFY IN THE LIST ON THE RIGHT >>"
                        disabled>
                    </div>
                    @else
                        <!-- First Case -->
                        @isset($verify->user)
                        <input type="text" name="bereaved_ref_number"
                            class="form-control input-solid"
                            value="{{ $verify->user->membership_number }} - {{ $verify->user->first_name }} {{ $verify->user->last_name }}"
                            disabled>
                        @else
                        <input type="text" name="bereaved_ref_number"
                            class="form-control input-solid"
                            value=""
                            disabled>
                        @endisset

                        <input type="hidden" name="payment_id" value="{{ $verify->id }}" >
                    </div>

                    <div class="form-group my-4">
                        <label for="name">@lang('Payment Type')</label>
                        <input type="hidden" name="payment_type" value="Bereavement Fund">
                        <input type="text" class="form-control input-solid" id="app_name"
                            name="app_name" value="Bereavement Fund" disabled>
                    </div>

                    <div class="form-group my-4">
                        <label for="image">Proof of Payment</label>
                        @if (is_null($verify) || $verify === "DEFAULT")
                        @else
                        <img src="/upload/invoices/{{ $verify->image_name }}" width="100%"/>
                        @endif
                    </div>

                    @endif

                @if (!is_null($verify))
                <button type="submit" class="btn btn-success">
                    @lang('Verify This Invoice')
                </button>

                <a href="{{ route('invoice.destroy', $verify) }}"
                    class="btn btn-danger"
                    title="@lang('Delete Invoice')"
                    data-toggle="tooltip"
                    data-placement="top"
                    data-method="DELETE"
                    data-confirm-title="@lang('Please Confirm')"
                    data-confirm-text="@lang('Are you sure that you want to delete this invoice?')"
                    data-confirm-delete="@lang('Yes, delete record!')">
                        <i class="fas fa-trash"></i> @lang('Delete This Invoice')
                </a>
                @endif

            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <h6 class="card-header d-flex align-items-center justify-content-between">
                @lang('Payments Awaiting Verification')
            </h6>
            <div class="card-body">
                <div class="table-responsive" id="users-table-wrapper">
                    <table class="table table-borderless table-striped">
                        <thead>
                        <tr>
                            {{-- <th class="min-width-80">@lang('Proof')</th> --}}
                            {{-- <th class="min-width-150">@lang('Amount')</th> --}}
                            <th class="min-width-100">@lang('Type')</th>
                            <th class="min-width-100">@lang('Reference')</th>
                            {{-- <th class="min-width-80">@lang('Authorised By')</th> --}}
                            <th class="min-width-80">@lang('Payer')</th>
                            <th class="min-width-80">@lang('Action')</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)

                            <tr>
                                {{-- <td class="align-middle">
                                    @if (isset($payment->payment_type) && $payment->payment_type == "Bereaved")
                                        <img src="/upload/invoices/8234.jpg" width="100"/>
                                    @else
                                        <img src="/upload/invoices/{{ $payment->image_name }}" width="100"/>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $payment->amount ?: __('N/A') }} </td> --}}
                                <td class="align-middle">{{ $payment->payment_type ?: __('N/A') }} </td>
                                <td class="align-middle">{{ $payment->reference ?: __(' - ') }}</td>
                                <!-- Second Case -->
                                <td class="align-middle">
                                    @if ($payment->user_id <= 9)
                                        PAM000{{ $payment->user_id }}
                                    @elseif ($payment->user_id >= 10 && $payment->user_id <= 99)
                                        PAM00{{ $payment->user_id }}
                                    @elseif ($payment->user_id >= 100 && $payment->user_id <= 999)
                                        PAM0{{ $payment->user_id }}
                                    @else
                                        PAM{{ $payment->user_id }}
                                    @endif
                                    {{-- @if($payment->reference)
                                        {{ $payment->reference }}
                                    @elseif(isset($payment->user) && !is_null($payment->user->membership_number))
                                        {{ $payment->user->membership_number }}
                                    @else
                                        null
                                    @endif --}}
                                </td>
                                {{-- <td class="align-middle">{{ $payment->reference ?: @if (!is_null($payment->user->membership_number)) $payment->user->membership_number @else null @endif }}</td> --}}
                                {{-- <td class="align-middle">
                                    @if (isset($payment->authorised_by) && $payment->authorised_by)
                                        <span class="badge badge-lg badge-success">
                                            {{ $payment->authorised_by }}
                                        </span>
                                    @else
                                        <span class="badge badge-lg badge-warning">
                                            Unassigned
                                        </span>
                                    @endif
                                </td> --}}
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
                                <td>
                                    <a href="{{ route('verify-invoices', $payment) }}" class="dropdown-item text-gray-500">
                                        <i class="fas fa-eye mr-2"></i>
                                        {{-- @lang('View') --}}
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
