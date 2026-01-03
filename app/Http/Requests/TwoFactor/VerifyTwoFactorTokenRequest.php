<?php

namespace App\Http\Requests\TwoFactor;

class VerifyTwoFactorTokenRequest extends TwoFactorRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required',
        ];
    }
}
