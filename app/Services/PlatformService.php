<?php

namespace App\Services;

use App\Enums\PlatformType;

class PlatformService
{
    public string $platform;

    public function __construct()
    {
        $this->platform = strtolower(request()->header('X-Platform'));
    }

    public function isWeb() {
        return $this->platform == PlatformType::WEB->value;
    }

    public function isSms() {
        return $this->platform == PlatformType::SMS->value;
    }

    public function isUssd() {
        return $this->platform == PlatformType::USSD->value;
    }

    public function isMobile() {
        return $this->platform == PlatformType::MOBILE->value;
    }
}
