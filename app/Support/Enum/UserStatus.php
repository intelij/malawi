<?php

namespace App\Support\Enum;

enum UserStatus: string
{
    case UNCONFIRMED = 'Unconfirmed';
    case ACTIVE = 'Active';
    case BANNED = 'Banned';
    case WAITING_APPROVAL = 'Waiting Approval';

    public static function lists(): array
    {
        $statuses = [
            self::ACTIVE->value => trans('app.status.'.self::ACTIVE->value),
            self::BANNED->value => trans('app.status.'.self::BANNED->value),
            self::UNCONFIRMED->value => trans('app.status.'.self::UNCONFIRMED->value),
        ];

        if (setting('approval.enabled')) {
            $statuses[self::WAITING_APPROVAL->value] = trans('app.status.'.self::WAITING_APPROVAL->value);
        }

        return $statuses;
    }
}
