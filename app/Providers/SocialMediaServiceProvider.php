<?php

namespace App\Providers;

use App\Services\SocialMedia\{
    BaseSocialMediaService,
    FacebookService,
    TwitterService,
    InstagramService,
    LinkedInService,
    TikTokService,
    YouTubeService
};
use Illuminate\Support\ServiceProvider;

class SocialMediaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Facebook Service
        $this->app->bind(FacebookService::class, function ($app) {
            return new FacebookService();
        });

        // Register Twitter Service
        $this->app->bind(TwitterService::class, function ($app) {
            return new TwitterService();
        });

        // Register Instagram Service
        $this->app->bind(InstagramService::class, function ($app) {
            return new InstagramService();
        });

        // Register LinkedIn Service
        $this->app->bind(LinkedInService::class, function ($app) {
            return new LinkedInService();
        });

        // Register TikTok Service
        $this->app->bind(TikTokService::class, function ($app) {
            return new TikTokService();
        });

        // Register YouTube Service
        $this->app->bind(YouTubeService::class, function ($app) {
            return new YouTubeService();
        });

        // Register Social Media Service Factory
        $this->app->bind('social-media', function ($app) {
            return new class {
                public function platform(string $platform): BaseSocialMediaService
                {
                    $serviceClass = match (strtolower($platform)) {
                        'facebook' => FacebookService::class,
                        'twitter' => TwitterService::class,
                        'instagram' => InstagramService::class,
                        'linkedin' => LinkedInService::class,
                        'tiktok' => TikTokService::class,
                        'youtube' => YouTubeService::class,
                        default => throw new \InvalidArgumentException("Unsupported platform: {$platform}"),
                    };

                    return app($serviceClass);
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register platform validators
        $this->registerPlatformValidators();
    }

    /**
     * Register custom validators for social media platforms
     */
    protected function registerPlatformValidators(): void
    {
        \Validator::extend('facebook_content', function ($attribute, $value, $parameters, $validator) {
            return mb_strlen($value) <= 63206; // Facebook's character limit
        });

        \Validator::extend('twitter_content', function ($attribute, $value, $parameters, $validator) {
            return mb_strlen($value) <= 280; // Twitter's character limit
        });

        \Validator::extend('instagram_content', function ($attribute, $value, $parameters, $validator) {
            return mb_strlen($value) <= 2200; // Instagram's character limit
        });

        \Validator::extend('linkedin_content', function ($attribute, $value, $parameters, $validator) {
            return mb_strlen($value) <= 3000; // LinkedIn's character limit
        });

        \Validator::extend('tiktok_content', function ($attribute, $value, $parameters, $validator) {
            return mb_strlen($value) <= 2200; // TikTok's character limit
        });

        \Validator::extend('youtube_content', function ($attribute, $value, $parameters, $validator) {
            return mb_strlen($value) <= 5000; // YouTube's description limit
        });

        // Add error messages for validators
        \Validator::replacer('facebook_content', function ($message, $attribute, $rule, $parameters) {
            return "The {$attribute} must not exceed 63,206 characters for Facebook.";
        });

        \Validator::replacer('twitter_content', function ($message, $attribute, $rule, $parameters) {
            return "The {$attribute} must not exceed 280 characters for Twitter.";
        });

        \Validator::replacer('instagram_content', function ($message, $attribute, $rule, $parameters) {
            return "The {$attribute} must not exceed 2,200 characters for Instagram.";
        });

        \Validator::replacer('linkedin_content', function ($message, $attribute, $rule, $parameters) {
            return "The {$attribute} must not exceed 3,000 characters for LinkedIn.";
        });

        \Validator::replacer('tiktok_content', function ($message, $attribute, $rule, $parameters) {
            return "The {$attribute} must not exceed 2,200 characters for TikTok.";
        });

        \Validator::replacer('youtube_content', function ($message, $attribute, $rule, $parameters) {
            return "The {$attribute} must not exceed 5,000 characters for YouTube.";
        });
    }
}
