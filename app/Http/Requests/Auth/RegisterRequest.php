<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use App\Support\Enum\UserStatus;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'email' => 'required|email|unique:users,email',
            // 'username' => 'required|unique:users,username',
            'password' => 'required|confirmed|min:8',
        ];

        if (setting('registration.captcha.enabled')) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        if (setting('tos')) {
            $rules['tos'] = 'accepted';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tos.accepted' => __('You have to accept Terms of Service.'),
        ];
    }

    public function validFormData(): array
    {
        // Determine user status. User's status will be set to UNCONFIRMED
        // if he has to confirm his email or to ACTIVE if email confirmation is not required

        if (setting('reg_email_confirmation')) {
            $status = UserStatus::UNCONFIRMED;
        } elseif (setting('approval.enabled')) {
            $status = UserStatus::WAITING_APPROVAL;
        } else {
            $status = UserStatus::ACTIVE;
        }

        return array_merge($this->only('email', 'password', 'first_name', 'last_name', 'phone', 'birthday'), [
            'status' => $status,
            'email_verified_at' => setting('reg_email_confirmation') ? null : now(),
        ]);
    }
}
