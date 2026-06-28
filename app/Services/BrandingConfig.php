<?php

namespace App\Services;

class BrandingConfig
{
    public function all(): array
    {
        return [
            'app_name' => config('app.name'),
            'logo_url' => (string) config('branding.logo_url', ''),
            'banner_url' => (string) config('branding.banner_url', ''),
            'primary_color' => (string) config('branding.primary_color', '#4F46E5'),
            'favicon_url' => (string) config('branding.favicon_url', ''),
        ];
    }
}
