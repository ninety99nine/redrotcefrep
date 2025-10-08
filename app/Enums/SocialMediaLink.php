<?php

namespace App\Enums;

enum SocialMediaLink: string
{
    case WHATSAPP = 'Whatsapp';
    case FACEBOOK = 'Facebook';
    case INSTAGRAM = 'Instagram';
    case TIKTOK = 'Tiktok';
    case YOUTUBE = 'YouTube';
    case LINKEDIN = 'LinkedIn';
    case MESSENGER = 'Messenger';
    case TELEGRAM = 'Telegram';
    case SNAPCHAT = 'Snapchat';
    case TWITCH = 'Twitch';
    case X = 'X';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
