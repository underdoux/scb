# Social Media Booster

A Laravel 12.x application for managing and automating social media posts across multiple platforms.

## Features

- User Authentication
- Social Media Integration (Facebook, Twitter, Instagram, LinkedIn, TikTok, YouTube)
- AI-Powered Content Generation with OpenAI
- Post Scheduling and Automation
- Analytics Dashboard
- Activity Logging

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite
- XAMPP (for local development)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd social-media-booster
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
- OpenAI API Key
- Social Media API Credentials (Facebook, Twitter, etc.)
- Database Configuration (SQLite)

7. Create SQLite database:
```bash
touch database/database.sqlite
```

8. Run migrations and seed the database:
```bash
php artisan migrate:fresh --seed
```

9. Build frontend assets:
```bash
npm run build
```

10. Start the development server:
```bash
php artisan serve
```

## Default Test Account

- Email: test@example.com
- Password: password

## Social Media Platform Setup

### Facebook
1. Create a Facebook Developer account
2. Create a new app
3. Configure OAuth settings
4. Add required permissions
5. Set callback URL: `http://localhost:8000/social-accounts/callback/facebook`

### Twitter
1. Apply for Twitter Developer account
2. Create a new project and app
3. Configure OAuth 2.0
4. Set callback URL: `http://localhost:8000/social-accounts/callback/twitter`

### LinkedIn
1. Create LinkedIn Developer account
2. Create a new app
3. Configure OAuth 2.0
4. Set callback URL: `http://localhost:8000/social-accounts/callback/linkedin`

### Instagram
1. Use Facebook Developer account
2. Configure Instagram Basic Display API
3. Set callback URL: `http://localhost:8000/social-accounts/callback/instagram`

### TikTok
1. Create TikTok Developer account
2. Create new application
3. Configure OAuth settings
4. Set callback URL: `http://localhost:8000/social-accounts/callback/tiktok`

### YouTube
1. Set up Google Cloud Project
2. Enable YouTube Data API
3. Configure OAuth consent screen
4. Set callback URL: `http://localhost:8000/social-accounts/callback/youtube`

## Scheduling Posts

To enable automatic post scheduling:

1. Configure cron job (Linux/Mac):
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

2. Or use Windows Task Scheduler:
```bash
schtasks /create /tn "Social Media Booster Schedule" /tr "php C:\path-to-your-project\artisan schedule:run" /sc minute
```

## Development

1. Start development server:
```bash
php artisan serve
```

2. Watch for frontend changes:
```bash
npm run dev
```

## Testing

Run the test suite:
```bash
php artisan test
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
