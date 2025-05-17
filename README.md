# Social Media Booster

A Laravel 12.x application for managing and automating social media posts across multiple platforms.

## Features

- User Authentication
- Social Media Integration (Facebook, Instagram, Twitter, LinkedIn, TikTok, YouTube)
- AI-Powered Content Generation using OpenAI
- Post Scheduling and Automation
- Analytics Dashboard
- Multi-platform Post Management
- Real-time Notifications

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite
- XAMPP (for local development)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd scb
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your .env file with:
- OpenAI API key
- Social Media API credentials
- Mail settings
- Queue settings

7. Create SQLite database:
```bash
touch database/database.sqlite
```

8. Run migrations:
```bash
php artisan migrate
```

9. Build frontend assets:
```bash
npm run build
```

## Configuration

### Social Media Platforms

Configure your social media API credentials in the .env file:

```env
# Facebook
FACEBOOK_CLIENT_ID=your_client_id
FACEBOOK_CLIENT_SECRET=your_client_secret
FACEBOOK_REDIRECT_URI="${APP_URL}/auth/facebook/callback"

# Instagram
INSTAGRAM_CLIENT_ID=your_client_id
INSTAGRAM_CLIENT_SECRET=your_client_secret
INSTAGRAM_REDIRECT_URI="${APP_URL}/auth/instagram/callback"

# Twitter
TWITTER_CLIENT_ID=your_client_id
TWITTER_CLIENT_SECRET=your_client_secret
TWITTER_REDIRECT_URI="${APP_URL}/auth/twitter/callback"

# LinkedIn
LINKEDIN_CLIENT_ID=your_client_id
LINKEDIN_CLIENT_SECRET=your_client_secret
LINKEDIN_REDIRECT_URI="${APP_URL}/auth/linkedin/callback"

# TikTok
TIKTOK_CLIENT_ID=your_client_id
TIKTOK_CLIENT_SECRET=your_client_secret
TIKTOK_REDIRECT_URI="${APP_URL}/auth/tiktok/callback"

# YouTube
YOUTUBE_CLIENT_ID=your_client_id
YOUTUBE_CLIENT_SECRET=your_client_secret
YOUTUBE_REDIRECT_URI="${APP_URL}/auth/youtube/callback"
```

### OpenAI Configuration

Set your OpenAI API key in the .env file:

```env
OPENAI_API_KEY=your_api_key
```

## Queue Worker Setup

1. Configure supervisor:
```bash
sudo cp supervisor.conf /etc/supervisor/conf.d/scb-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start scb-worker:*
```

2. Or run queue worker manually:
```bash
php artisan queue:work
```

## Scheduler Setup

Add the following Cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Usage

1. Register an account at `/register`
2. Connect your social media accounts
3. Create posts using AI-generated content or write your own
4. Schedule posts for automatic publishing
5. Monitor post performance through the dashboard

## Development

For local development:

1. Start the development server:
```bash
php artisan serve
```

2. Watch for asset changes:
```bash
npm run dev
```

## Testing

Run the test suite:

```bash
php artisan test
```

## License

This project is licensed under the MIT License.
