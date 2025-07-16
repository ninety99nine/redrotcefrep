<?php

namespace App\Enums;

enum SocialMediaLink: string
{
    case WHATSAPP = 'Whatsapp';
    case TELEGRAM = 'Telegram';
    case MESSENGER = 'Messenger';
    case FACEBOOK = 'Facebook';
    case INSTAGRAM = 'Instagram';
    case LINKEDIN = 'LinkedIn';
    case YOUTUBE = 'YouTube';
    case SNAPCHAT = 'Snapchat';
    case TIKTOK = 'Tiktok';
    case TWITCH = 'Twitch';
    case X = 'X';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
