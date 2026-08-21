<?php

namespace App\Providers;

use App\Models\GalleryPhoto;
use App\Models\SiteImage;
use Illuminate\Support\Facades\Schema;
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
            'movies'         => 'banner_gallery',
            'gallery-upload' => 'banner_gallery',
            'login'          => 'login_bg',
        ];

        View::composer(array_keys($locationMap), function ($view) use ($locationMap) {
            $name = $view->getName();
            if (! isset($locationMap[$name])) {
                return;
            }

            $siteBanner = SiteImage::forDisplay($locationMap[$name]);
            $uploadedBanners = $name === 'login' ? collect() : $this->randomUploadedGalleryPhotos(10);
            $uploadedBanner = $uploadedBanners->first();
            $bannerImages = $uploadedBanners->isNotEmpty()
                ? $uploadedBanners
                : collect($siteBanner ? [$siteBanner] : []);

            $view->with('siteBannerImage', $siteBanner);
            $view->with('randomBannerImage', $uploadedBanner);
            $view->with('bannerImage', $uploadedBanner ?: $siteBanner);
            $view->with('bannerImages', $bannerImages);
        });
    }

    private function randomUploadedGalleryPhotos(int $limit)
    {
        try {
            if (! Schema::hasTable('gallery_photos')) {
                return collect();
            }

            $taggedPhotos = GalleryPhoto::query()
                ->where('is_active', true)
                ->where('status', 'approved')
                ->whereNotNull('file_path')
                ->where(function ($query) {
                    $query->whereHas('taggedUsers')
                        ->orWhereHas('taggedGroups');
                })
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            if ($taggedPhotos->count() >= $limit) {
                return $taggedPhotos;
            }

            $remaining = $limit - $taggedPhotos->count();
            $fallbackPhotos = GalleryPhoto::query()
                ->where('is_active', true)
                ->where('status', 'approved')
                ->whereNotNull('file_path')
                ->whereNotIn('id', $taggedPhotos->pluck('id'))
                ->inRandomOrder()
                ->limit($remaining)
                ->get();

            return $taggedPhotos->concat($fallbackPhotos)->values();
        } catch (\Throwable) {
            return collect();
        }
    }
}
