<?php

namespace App\Providers;

use App\Services\SocialMedia\FacebookService;
use App\Services\SocialMedia\InstagramService;
use App\Services\SocialMedia\LinkedInService;
use App\Services\SocialMedia\TikTokService;
use App\Services\SocialMedia\TwitterService;
use App\Services\SocialMedia\YouTubeService;
use Illuminate\Support\ServiceProvider;

class SocialMediaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Twitter Service
        $this->app->singleton(TwitterService::class, function ($app) {
            return new TwitterService();
        });

        // Register Facebook Service
        $this->app->singleton(FacebookService::class, function ($app) {
            return new FacebookService();
        });

        // Register Instagram Service
        $this->app->singleton(InstagramService::class, function ($app) {
            return new InstagramService();
        });

        // Register LinkedIn Service
        $this->app->singleton(LinkedInService::class, function ($app) {
            return new LinkedInService();
        });

        // Register TikTok Service
        $this->app->singleton(TikTokService::class, function ($app) {
            return new TikTokService();
        });

        // Register YouTube Service
        $this->app->singleton(YouTubeService::class, function ($app) {
            return new YouTubeService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
