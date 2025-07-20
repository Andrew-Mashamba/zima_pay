<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the ZIMAESB API
    | security system including authentication, rate limiting, encryption,
    | and threat detection settings.
    |
    */

    'api' => [
        /*
        |--------------------------------------------------------------------------
        | Authentication Settings
        |--------------------------------------------------------------------------
        */
        'authentication' => [
            'timestamp_tolerance' => env('API_TIMESTAMP_TOLERANCE', 300), // 5 minutes
            'nonce_cache_duration' => env('API_NONCE_CACHE_DURATION', 600), // 10 minutes
            'signature_algorithm' => env('API_SIGNATURE_ALGORITHM', 'sha256'),
            'enforce_https' => env('API_ENFORCE_HTTPS', true),
        ],

        /*
        |--------------------------------------------------------------------------
        | Rate Limiting Settings
        |--------------------------------------------------------------------------
        */
        'rate_limiting' => [
            'global' => [
                'requests_per_hour' => env('API_GLOBAL_RATE_LIMIT', 1000),
                'burst_limit' => env('API_BURST_LIMIT', 10),
                'burst_window' => env('API_BURST_WINDOW', 10), // seconds
            ],
            'client' => [
                'default_per_minute' => env('API_CLIENT_RATE_MINUTE', 60),
                'default_per_hour' => env('API_CLIENT_RATE_HOUR', 3600),
                'default_per_day' => env('API_CLIENT_RATE_DAY', 86400),
            ],
            'endpoint' => [
                'transactions' => env('API_ENDPOINT_TRANSACTIONS_LIMIT', 100),
                'balance' => env('API_ENDPOINT_BALANCE_LIMIT', 20),
                'status' => env('API_ENDPOINT_STATUS_LIMIT', 10),
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | IP Security Settings
        |--------------------------------------------------------------------------
        */
        'ip_security' => [
            'enable_whitelist' => env('API_ENABLE_IP_WHITELIST', false),
            'auto_block_threshold' => env('API_AUTO_BLOCK_THRESHOLD', 5),
            'block_duration' => env('API_BLOCK_DURATION', 7200), // 2 hours
            'permanent_block_threshold' => env('API_PERMANENT_BLOCK_THRESHOLD', 10),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Settings
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'algorithm' => env('SECURITY_ENCRYPTION_ALGORITHM', 'aes-256-gcm'),
        'key_rotation_days' => env('SECURITY_KEY_ROTATION_DAYS', 30),
        'backup_key_count' => env('SECURITY_BACKUP_KEY_COUNT', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Threat Detection Settings
    |--------------------------------------------------------------------------
    */
    'threat_detection' => [
        'enable' => env('SECURITY_THREAT_DETECTION', true),
        'patterns' => [
            'sql_injection' => env('SECURITY_DETECT_SQL_INJECTION', true),
            'xss_attacks' => env('SECURITY_DETECT_XSS', true),
            'path_traversal' => env('SECURITY_DETECT_PATH_TRAVERSAL', true),
            'command_injection' => env('SECURITY_DETECT_COMMAND_INJECTION', true),
        ],
        'behavioral' => [
            'rapid_requests_threshold' => env('SECURITY_RAPID_REQUESTS_THRESHOLD', 100),
            'endpoint_enumeration_threshold' => env('SECURITY_ENDPOINT_ENUM_THRESHOLD', 20),
            'error_rate_threshold' => env('SECURITY_ERROR_RATE_THRESHOLD', 10),
        ],
        'auto_response' => [
            'block_critical_threats' => env('SECURITY_AUTO_BLOCK_CRITICAL', true),
            'block_high_risk_score' => env('SECURITY_AUTO_BLOCK_HIGH_RISK', 50),
            'monitor_medium_threats' => env('SECURITY_MONITOR_MEDIUM', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Alerting
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enable_logging' => env('SECURITY_ENABLE_LOGGING', true),
        'log_level' => env('SECURITY_LOG_LEVEL', 'info'),
        'alert_emails' => array_filter(explode(',', env('SECURITY_ALERT_EMAILS', ''))),
        'alert_webhook' => env('SECURITY_ALERT_WEBHOOK'),
        'slack_webhook' => env('SECURITY_SLACK_WEBHOOK'),
        'pagerduty_key' => env('SECURITY_PAGERDUTY_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance Settings
    |--------------------------------------------------------------------------
    */
    'compliance' => [
        'audit_retention_days' => env('SECURITY_AUDIT_RETENTION_DAYS', 365),
        'log_retention_days' => env('SECURITY_LOG_RETENTION_DAYS', 90),
        'encrypt_logs' => env('SECURITY_ENCRYPT_LOGS', true),
        'gdpr_compliance' => env('SECURITY_GDPR_COMPLIANCE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Geographic Restrictions
    |--------------------------------------------------------------------------
    */
    'geographic' => [
        'enable' => env('SECURITY_GEOGRAPHIC_RESTRICTIONS', false),
        'allowed_countries' => array_filter(explode(',', env('SECURITY_ALLOWED_COUNTRIES', 'TZ,KE,UG,RW'))),
        'blocked_countries' => array_filter(explode(',', env('SECURITY_BLOCKED_COUNTRIES', ''))),
        'geoip_provider' => env('SECURITY_GEOIP_PROVIDER', 'maxmind'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Security
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'signature_header' => env('WEBHOOK_SIGNATURE_HEADER', 'X-Webhook-Signature'),
        'timestamp_header' => env('WEBHOOK_TIMESTAMP_HEADER', 'X-Webhook-Timestamp'),
        'signature_algorithm' => env('WEBHOOK_SIGNATURE_ALGORITHM', 'sha256'),
        'tolerance_seconds' => env('WEBHOOK_TOLERANCE_SECONDS', 300),
        'retry_attempts' => env('WEBHOOK_RETRY_ATTEMPTS', 3),
        'timeout_seconds' => env('WEBHOOK_TIMEOUT_SECONDS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'strict_transport_security' => env('SECURITY_HSTS', 'max-age=31536000; includeSubDomains'),
        'content_security_policy' => env('SECURITY_CSP', "default-src 'self'"),
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'DENY'),
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

];