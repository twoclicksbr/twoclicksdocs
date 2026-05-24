<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'webhook' => [
        'code_secret' => env('WEBHOOK_CODE_SECRET'),
    ],

    'claude' => [
        'bin'         => env('CLAUDE_BIN', 'claude'),
        'project_dir' => env('CLAUDE_PROJECT_DIR', base_path()),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sandbox dump (admin → restaurar sandbox a partir de dump de prod)
    |--------------------------------------------------------------------------
    | Usado pelo RestoreSandboxFromProdDumpJob (task #78). Como prod e sandbox
    | dividem o mesmo PostgreSQL local e o mesmo user, basta o nome do DB
    | de origem. As demais credenciais reutilizam database.connections.tc_doc.
    */
    'sandbox_dump' => [
        'prod_db'     => env('SANDBOX_DUMP_PROD_DB', 'tc_doc'),
        'pg_dump_bin' => env('SANDBOX_DUMP_PG_DUMP_BIN', '/usr/bin/pg_dump'),
        'psql_bin'    => env('SANDBOX_DUMP_PSQL_BIN', '/usr/bin/psql'),
    ],

];
