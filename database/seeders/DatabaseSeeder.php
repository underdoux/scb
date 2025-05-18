<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\Log;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create sample social accounts
        $socialAccounts = [
            [
                'platform' => 'facebook',
                'platform_user_id' => '123456789',
                'platform_username' => 'testuser.fb',
                'access_token' => 'sample_fb_token',
                'token_expires_at' => now()->addDays(60),
            ],
            [
                'platform' => 'twitter',
                'platform_user_id' => '987654321',
                'platform_username' => '@testuser',
                'access_token' => 'sample_twitter_token',
                'token_expires_at' => now()->addDays(30),
            ],
            [
                'platform' => 'instagram',
                'platform_user_id' => '456789123',
                'platform_username' => 'testuser.ig',
                'access_token' => 'sample_ig_token',
                'token_expires_at' => now()->addDays(45),
            ],
        ];

        foreach ($socialAccounts as $accountData) {
            $user->socialAccounts()->create($accountData);
        }

        // Create sample posts
        $posts = [
            [
                'platform' => 'facebook',
                'content' => 'Excited to announce our latest product launch! 🚀 #Innovation #Technology',
                'status' => 'published',
                'published_at' => now()->subHours(2),
            ],
            [
                'platform' => 'twitter',
                'content' => 'Just wrapped up an amazing team meeting! Great things coming soon... 💡',
                'status' => 'published',
                'published_at' => now()->subHours(1),
            ],
            [
                'platform' => 'instagram',
                'content' => 'Behind the scenes look at our creative process 📸 #BehindTheScenes #Creative',
                'status' => 'scheduled',
                'published_at' => now()->addHours(2),
            ],
            [
                'platform' => 'facebook',
                'content' => 'Join us for our upcoming webinar on digital transformation!',
                'status' => 'draft',
            ],
        ];

        foreach ($posts as $postData) {
            $post = $user->posts()->create($postData);

            // Create schedule for scheduled posts
            if ($postData['status'] === 'scheduled') {
                $post->schedule()->create([
                    'scheduled_time' => $postData['published_at'],
                    'status' => 'pending',
                ]);
            }
        }

        // Create sample logs
        $logs = [
            [
                'type' => 'info',
                'message' => 'Successfully connected Facebook account',
                'context' => ['platform' => 'facebook'],
            ],
            [
                'type' => 'success',
                'message' => 'Post published successfully',
                'context' => ['platform' => 'twitter', 'post_id' => 2],
            ],
            [
                'type' => 'warning',
                'message' => 'Token expiring soon',
                'context' => ['platform' => 'instagram'],
            ],
            [
                'type' => 'error',
                'message' => 'Failed to publish post',
                'context' => ['platform' => 'linkedin', 'error' => 'Token expired'],
            ],
        ];

        foreach ($logs as $logData) {
            Log::create(array_merge($logData, [
                'user_id' => $user->id,
                'post_id' => $logData['context']['post_id'] ?? null,
            ]));
        }

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        // Output seeding completion message
        $this->command->info('Database seeded with test data:');
        $this->command->info('- Test User: test@example.com / password');
        $this->command->info('- Admin User: admin@example.com / password');
        $this->command->info('- Sample social accounts, posts, and logs created');
    }
}
