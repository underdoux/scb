<?php
// OAuth Configuration
return [
    'facebook' => [
        'client_id' => '', // Your Facebook App ID
        'client_secret' => '', // Your Facebook App Secret
        'redirect_uri' => 'http://localhost:8000/oauth/facebook/callback',
        'scopes' => ['pages_show_list', 'pages_read_engagement', 'instagram_basic'],
        'auth_url' => 'https://www.facebook.com/v18.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v18.0/oauth/access_token'
    ],
    'twitter' => [
        'client_id' => '', // Your Twitter API Key
        'client_secret' => '', // Your Twitter API Secret
        'redirect_uri' => 'http://localhost:8000/oauth/twitter/callback',
        'scopes' => ['tweet.read', 'tweet.write', 'users.read'],
        'auth_url' => 'https://twitter.com/i/oauth2/authorize',
        'token_url' => 'https://api.twitter.com/2/oauth2/token'
    ],
    'linkedin' => [
        'client_id' => '', // Your LinkedIn Client ID
        'client_secret' => '', // Your LinkedIn Client Secret
        'redirect_uri' => 'http://localhost:8000/oauth/linkedin/callback',
        'scopes' => ['r_liteprofile', 'w_member_social'],
        'auth_url' => 'https://www.linkedin.com/oauth/v2/authorization',
        'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken'
    ]
];
?>
