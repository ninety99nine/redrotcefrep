<?php

namespace App\Enums;

enum Association:string {
    case SHOPPER = 'shopper';
    case FOLLOWER = 'follower';
    case CUSTOMER = 'customer';
    case TEAM_MEMBER = 'team member';
    case SUPER_ADMIN = 'super admin';
    case RECENT_VISITOR = 'recent visitor';

    case ASSOCIATED = 'associated';
    case UNASSOCIATED = 'unassociated';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
