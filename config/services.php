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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    // Em config/services.php

    'google' => [
        // Credenciais de Autenticação
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'admin_email' => env('GOOGLE_ADMIN_ACCOUNT_EMAIL'),

        // ID da pasta principal no Drive para salvar os documentos
        'drive_folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),

        // Configurações do Google Sheets
        'sheets' => [
            'data_collection_id' => env('GOOGLE_SHEET_ID_DATA_COLLECTION'),
            'supervisor_evaluation_id' => env('GOOGLE_SHEET_ID_SUPERVISOR_EVALUATION'),
        ],

        // Configurações do Google Forms
        'forms' => [
            'data_collection_id' => env('GOOGLE_FORM_ID_DATA_COLLECTION'),
            'advisors_question_id' => env('GOOGLE_FORM_QUESTION_ID_ADVISORS'),
        ],

        // Configurações do Google Docs
        'docs' => [
            'templates' => [
                'termo_compromisso_padrao' => env('GOOGLE_DOCS_TEMPLATE_ID_TERMO_COMPROMISSO_PADRAO'),
                'termo_emater_rs' => env('GOOGLE_DOCS_TEMPLATE_ID_TERMO_EMATER_RS'),
                'termo_seduc' => env('GOOGLE_DOCS_TEMPLATE_ID_TERMO_SEDUC'),
                'rescisao' => env('GOOGLE_DOCS_TEMPLATE_ID_RESCISAO'),
                'credenciamento' => env('GOOGLE_DOCS_TEMPLATE_ID_CREDENCIAMENTO'),
            ],
        ],
    ],

];
