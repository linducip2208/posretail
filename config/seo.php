<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Indexing API
    |--------------------------------------------------------------------------
    |
    | Optional. Provide the absolute path to a Google service account JSON key
    | file (with the Indexing API enabled and the service account added as an
    | owner in Google Search Console). Leave empty to disable — the service
    | degrades gracefully to a no-op when not configured.
    |
    */

    'google_indexing' => [
        'credentials_path' => env('GOOGLE_INDEXING_CREDENTIALS', storage_path('app/google-indexing.json')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Broadcast Webhook
    |--------------------------------------------------------------------------
    |
    | Optional outbound webhook fired when new content is published. Point it
    | at any automation endpoint (Zapier / Make / n8n / self-hosted) to fan out
    | to social platforms without hardcoding any provider. Leave empty to skip.
    |
    */

    'broadcast_webhook' => env('SEO_BROADCAST_WEBHOOK', ''),

];
