<?php

namespace App\Livewire\SecuritySettings;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SecuritySettings extends Component
{
    // Selected menu item for sidebar navigation
    public $selectedMenuItem = 1;
    
    // Authentication Settings
    public $timestampTolerance = 300;
    public $nonceCacheDuration = 600;
    public $enforceHttps = true;
    public $signatureAlgorithm = 'sha256';

    // Rate Limiting Settings
    public $globalRateLimit = 1000;
    public $burstLimit = 10;
    public $burstWindow = 10;
    public $defaultPerMinute = 60;
    public $defaultPerHour = 3600;
    public $defaultPerDay = 86400;

    // IP Security Settings
    public $enableWhitelist = false;
    public $autoBlockThreshold = 5;
    public $blockDuration = 7200;
    public $permanentBlockThreshold = 10;

    // Threat Detection Settings
    public $enableThreatDetection = true;
    public $detectSqlInjection = true;
    public $detectXss = true;
    public $detectPathTraversal = true;
    public $detectCommandInjection = true;
    public $rapidRequestsThreshold = 100;
    public $endpointEnumThreshold = 20;
    public $errorRateThreshold = 10;

    // Encryption Settings
    public $encryptionAlgorithm = 'aes-256-gcm';
    public $keyRotationDays = 30;
    public $backupKeyCount = 3;

    // Monitoring Settings
    public $enableLogging = true;
    public $logLevel = 'info';
    public $alertEmails = '';
    public $alertWebhook = '';

    protected $rules = [
        'timestampTolerance' => 'required|integer|min:60|max:1800',
        'nonceCacheDuration' => 'required|integer|min:300|max:3600',
        'globalRateLimit' => 'required|integer|min:100|max:10000',
        'burstLimit' => 'required|integer|min:5|max:100',
        'burstWindow' => 'required|integer|min:5|max:60',
        'autoBlockThreshold' => 'required|integer|min:3|max:50',
        'blockDuration' => 'required|integer|min:1800|max:86400',
        'rapidRequestsThreshold' => 'required|integer|min:50|max:1000',
        'endpointEnumThreshold' => 'required|integer|min:10|max:100',
        'errorRateThreshold' => 'required|integer|min:5|max:100',
        'keyRotationDays' => 'required|integer|min:7|max:365',
        'backupKeyCount' => 'required|integer|min:1|max:10',
        'alertEmails' => 'nullable|string',
    ];

    public function mount()
    {
        $this->loadCurrentSettings();
    }

    public function loadCurrentSettings()
    {
        // Load current security configuration
        $config = config('security');

        // Authentication Settings
        $this->timestampTolerance = $config['api']['authentication']['timestamp_tolerance'] ?? 300;
        $this->nonceCacheDuration = $config['api']['authentication']['nonce_cache_duration'] ?? 600;
        $this->enforceHttps = $config['api']['authentication']['enforce_https'] ?? true;
        $this->signatureAlgorithm = $config['api']['authentication']['signature_algorithm'] ?? 'sha256';

        // Rate Limiting Settings
        $this->globalRateLimit = $config['api']['rate_limiting']['global']['requests_per_hour'] ?? 1000;
        $this->burstLimit = $config['api']['rate_limiting']['global']['burst_limit'] ?? 10;
        $this->burstWindow = $config['api']['rate_limiting']['global']['burst_window'] ?? 10;
        $this->defaultPerMinute = $config['api']['rate_limiting']['client']['default_per_minute'] ?? 60;
        $this->defaultPerHour = $config['api']['rate_limiting']['client']['default_per_hour'] ?? 3600;
        $this->defaultPerDay = $config['api']['rate_limiting']['client']['default_per_day'] ?? 86400;

        // IP Security Settings
        $this->enableWhitelist = $config['api']['ip_security']['enable_whitelist'] ?? false;
        $this->autoBlockThreshold = $config['api']['ip_security']['auto_block_threshold'] ?? 5;
        $this->blockDuration = $config['api']['ip_security']['block_duration'] ?? 7200;
        $this->permanentBlockThreshold = $config['api']['ip_security']['permanent_block_threshold'] ?? 10;

        // Threat Detection Settings
        $this->enableThreatDetection = $config['threat_detection']['enable'] ?? true;
        $this->detectSqlInjection = $config['threat_detection']['patterns']['sql_injection'] ?? true;
        $this->detectXss = $config['threat_detection']['patterns']['xss_attacks'] ?? true;
        $this->detectPathTraversal = $config['threat_detection']['patterns']['path_traversal'] ?? true;
        $this->detectCommandInjection = $config['threat_detection']['patterns']['command_injection'] ?? true;
        $this->rapidRequestsThreshold = $config['threat_detection']['behavioral']['rapid_requests_threshold'] ?? 100;
        $this->endpointEnumThreshold = $config['threat_detection']['behavioral']['endpoint_enumeration_threshold'] ?? 20;
        $this->errorRateThreshold = $config['threat_detection']['behavioral']['error_rate_threshold'] ?? 10;

        // Encryption Settings
        $this->encryptionAlgorithm = $config['encryption']['algorithm'] ?? 'aes-256-gcm';
        $this->keyRotationDays = $config['encryption']['key_rotation_days'] ?? 30;
        $this->backupKeyCount = $config['encryption']['backup_key_count'] ?? 3;

        // Monitoring Settings
        $this->enableLogging = $config['monitoring']['enable_logging'] ?? true;
        $this->logLevel = $config['monitoring']['log_level'] ?? 'info';
        $this->alertEmails = implode(',', $config['monitoring']['alert_emails'] ?? []);
        $this->alertWebhook = $config['monitoring']['alert_webhook'] ?? '';
    }

    public function saveSettings()
    {
        $this->validate();

        try {
            // Create updated configuration array
            $newConfig = $this->buildConfigArray();

            // Write to security configuration file
            $this->writeConfigFile($newConfig);

            // Clear configuration cache
            Cache::forget('config.security');

            // Log the configuration change
            DB::table('security_logs')->insert([
                'event_type' => 'configuration_updated',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => json_encode([
                    'updated_by' => auth()->user()->email,
                    'settings_changed' => array_keys($newConfig)
                ]),
                'severity' => 'medium',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            session()->flash('message', 'Security settings updated successfully.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update security settings: ' . $e->getMessage());
        }
    }

    private function buildConfigArray()
    {
        return [
            'api' => [
                'authentication' => [
                    'timestamp_tolerance' => $this->timestampTolerance,
                    'nonce_cache_duration' => $this->nonceCacheDuration,
                    'signature_algorithm' => $this->signatureAlgorithm,
                    'enforce_https' => $this->enforceHttps,
                ],
                'rate_limiting' => [
                    'global' => [
                        'requests_per_hour' => $this->globalRateLimit,
                        'burst_limit' => $this->burstLimit,
                        'burst_window' => $this->burstWindow,
                    ],
                    'client' => [
                        'default_per_minute' => $this->defaultPerMinute,
                        'default_per_hour' => $this->defaultPerHour,
                        'default_per_day' => $this->defaultPerDay,
                    ],
                ],
                'ip_security' => [
                    'enable_whitelist' => $this->enableWhitelist,
                    'auto_block_threshold' => $this->autoBlockThreshold,
                    'block_duration' => $this->blockDuration,
                    'permanent_block_threshold' => $this->permanentBlockThreshold,
                ],
            ],
            'threat_detection' => [
                'enable' => $this->enableThreatDetection,
                'patterns' => [
                    'sql_injection' => $this->detectSqlInjection,
                    'xss_attacks' => $this->detectXss,
                    'path_traversal' => $this->detectPathTraversal,
                    'command_injection' => $this->detectCommandInjection,
                ],
                'behavioral' => [
                    'rapid_requests_threshold' => $this->rapidRequestsThreshold,
                    'endpoint_enumeration_threshold' => $this->endpointEnumThreshold,
                    'error_rate_threshold' => $this->errorRateThreshold,
                ],
            ],
            'encryption' => [
                'algorithm' => $this->encryptionAlgorithm,
                'key_rotation_days' => $this->keyRotationDays,
                'backup_key_count' => $this->backupKeyCount,
            ],
            'monitoring' => [
                'enable_logging' => $this->enableLogging,
                'log_level' => $this->logLevel,
                'alert_emails' => array_filter(explode(',', $this->alertEmails)),
                'alert_webhook' => $this->alertWebhook,
            ],
        ];
    }

    private function writeConfigFile($config)
    {
        $configPath = config_path('security.php');
        
        // Merge with existing configuration to preserve other settings
        $existingConfig = include $configPath;
        $mergedConfig = array_merge_recursive($existingConfig, $config);
        
        // Generate the configuration file content
        $content = "<?php\n\nreturn " . var_export($mergedConfig, true) . ";\n";
        
        File::put($configPath, $content);
    }

    public function resetToDefaults()
    {
        // Reset all settings to default values
        $this->timestampTolerance = 300;
        $this->nonceCacheDuration = 600;
        $this->enforceHttps = true;
        $this->signatureAlgorithm = 'sha256';
        
        $this->globalRateLimit = 1000;
        $this->burstLimit = 10;
        $this->burstWindow = 10;
        $this->defaultPerMinute = 60;
        $this->defaultPerHour = 3600;
        $this->defaultPerDay = 86400;
        
        $this->enableWhitelist = false;
        $this->autoBlockThreshold = 5;
        $this->blockDuration = 7200;
        $this->permanentBlockThreshold = 10;
        
        $this->enableThreatDetection = true;
        $this->detectSqlInjection = true;
        $this->detectXss = true;
        $this->detectPathTraversal = true;
        $this->detectCommandInjection = true;
        $this->rapidRequestsThreshold = 100;
        $this->endpointEnumThreshold = 20;
        $this->errorRateThreshold = 10;
        
        $this->encryptionAlgorithm = 'aes-256-gcm';
        $this->keyRotationDays = 30;
        $this->backupKeyCount = 3;
        
        $this->enableLogging = true;
        $this->logLevel = 'info';
        $this->alertEmails = '';
        $this->alertWebhook = '';

        session()->flash('message', 'Security settings reset to defaults.');
    }

    public function testConfiguration()
    {
        // Perform a basic validation of the current configuration
        $errors = [];
        
        if ($this->timestampTolerance < 60) {
            $errors[] = 'Timestamp tolerance too low (minimum 60 seconds)';
        }
        
        if ($this->globalRateLimit < 100) {
            $errors[] = 'Global rate limit too low (minimum 100 requests/hour)';
        }
        
        if ($this->autoBlockThreshold < 3) {
            $errors[] = 'Auto-block threshold too low (minimum 3 violations)';
        }

        if (empty($errors)) {
            session()->flash('message', 'Configuration test passed - all settings are valid.');
        } else {
            session()->flash('error', 'Configuration test failed: ' . implode(', ', $errors));
        }
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
    }

    public function render()
    {
        return view('livewire.security-settings.security-settings');
    }
}