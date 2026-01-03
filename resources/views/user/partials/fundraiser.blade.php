<tr>
    <td class="align-middle">{{ optional($fundraise->user)->membership_number ?: __('N/A') }} </td>
    <td class="align-middle">{{ optional($fundraise->user)->first_name ?: __('N/A') }} {{ optional($fundraise->user)->last_name ?: __('N/A') }} </td>
    <td class="align-middle">{{ $fundraise->start_date }}</td>
    <td class="align-middle">{{ $fundraise->end_date }}</td>
    <td class="align-middle">{{ $fundraise->amount ?: __(' - ') }}</td>
    <td class="align-middle">
        @if (count($is_admin))
            <form action="{{ route('fundraiser.export') }}" method="POST" id="details-form">
                @csrf <!-- Include CSRF protection for POST requests -->
                <input type="hidden" name="membership_number" value="{{ optional($fundraise)->membership_number }}" />
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download
                </button>
            </form>
        @endif
    </td>
    <td class="align-middle">
        @if (count($is_admin))
            <a href="{{ route('fundraiser.destroy', $fundraise) }}"
            class="btn btn-icon"
            title="@lang('Delete Fundraiser')"
            data-toggle="tooltip"
            data-placement="top"
            data-method="DELETE"
            data-confirm-title="@lang('Please Confirm')"
            data-confirm-text="@lang('Are you sure that you want to delete this fundraiser?')"
            data-confirm-delete="@lang('Yes, delete record!')">
                <i class="fas fa-trash"></i>
        </a>
        @endif
    </td>
</tr>
