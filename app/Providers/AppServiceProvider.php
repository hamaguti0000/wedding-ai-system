<?php

namespace App\Providers;

use App\Models\SiteImage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $locationMap = [
            'access'         => 'banner_access',
            'faq'            => 'banner_faq',
            'gallery'        => 'banner_gallery',
            'guestbook'      => 'banner_guestbook',
            'invitation'     => 'banner_invitation',
            'news'           => 'banner_news',
            'news-show'      => 'banner_news',
            'program'        => 'banner_program',
            'profiles.index' => 'banner_profile',
            'profiles.show'  => 'banner_profile',
            'people.index'   => 'banner_gallery',
            'people.show'    => 'banner_gallery',
            'login'          => 'login_bg',
        ];

        View::composer(array_keys($locationMap), function ($view) use ($locationMap) {
            $name = $view->getName();
            if (isset($locationMap[$name])) {
                $view->with('bannerImage', SiteImage::forDisplay($locationMap[$name]));
            }
        });
    }
}
