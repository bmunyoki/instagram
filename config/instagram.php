<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Facebook APP ID
    |--------------------------------------------------------------------------
    |
    | Get Facebook App ID from .env file
    |
    */
    'app_id' => env('FACEBOOK_APP_ID', ''),
    /*
    |--------------------------------------------------------------------------
    | Facebook APP Secret
    |--------------------------------------------------------------------------
    |
    | Get Facebook App Secret from .env file
    |
    */
    'app_secret' => env('FACEBOOK_APP_SECRET', ''),
    /*
    |--------------------------------------------------------------------------
    | Instagram Redirect URL
    |--------------------------------------------------------------------------
    |
    | Get redirect URL from .env file
    |
    */
    'redirect_url' => env('INSTAGRAM_REDIRECT_URL', ''),
];