<?php

namespace App\Providers;

use App\Services\OpenAIService;
use Illuminate\Support\ServiceProvider;

class OpenAIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenAIService::class, function ($app) {
            return new OpenAIService();
        });

        // Register facade accessor
        $this->app->bind('openai', function ($app) {
            return $app->make(OpenAIService::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Validate OpenAI configuration
        $this->validateConfig();
    }

    /**
     * Validate the OpenAI configuration.
     */
    protected function validateConfig(): void
    {
        $required = [
            'services.openai.api_key' => 'OpenAI API key is not configured.',
        ];

        $optional = [
            'services.openai.model' => 'gpt-4',
            'services.openai.temperature' => 0.7,
            'services.openai.max_tokens' => 200,
        ];

        // Check required config values
        foreach ($required as $key => $message) {
            if (!config($key)) {
                \Log::warning($message);
            }
        }

        // Set default values for optional config
        foreach ($optional as $key => $default) {
            if (!config($key)) {
                config([$key => $default]);
            }
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            OpenAIService::class,
            'openai',
        ];
    }
}
