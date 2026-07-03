<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO Secret
    |--------------------------------------------------------------------------
    |
    | Shared secret dengan Holding App (HMAC-SHA256, raw binary output).
    | Harus identik di Holding + semua subsidiary. Regenerate di kedua sisi
    | secara bersamaan kalau bocor.
    |
    | lihat: .guide/sso-subsidiary-guide.md
    */
    'secret' => env('SSO_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | TTL token (detik)
    |--------------------------------------------------------------------------
    |
    | Harus match dengan setting di Holding. Default 5 menit.
    */
    'ttl' => (int) env('SSO_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Path SSO
    |--------------------------------------------------------------------------
    |
    | Path endpoint untuk consume token. Default: /auth/sso
    */
    'path' => env('SSO_PATH', '/auth/sso'),
];
