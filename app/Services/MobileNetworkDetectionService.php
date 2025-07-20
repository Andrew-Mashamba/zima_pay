<?php

namespace App\Services;

class MobileNetworkDetectionService
{
    /**
     * Tanzanian mobile network prefixes (2025)
     * Source: Tanzania Communications Regulatory Authority (TCRA)
     */
    private const NETWORK_PREFIXES = [
        'vodacom' => [
            'name' => 'Vodacom M-Pesa',
            'prefixes' => ['74', '75', '76'],
            'color' => '#E60000',
            'icon' => 'fas fa-mobile-alt',
            'network_code' => 'TZ-MPESA-C2B'
        ],
        'airtel' => [
            'name' => 'Airtel Money',
            'prefixes' => ['68', '69', '78', '79'],
            'color' => '#FF0000',
            'icon' => 'fas fa-mobile-alt',
            'network_code' => 'TZ-AIRTEL-C2B'
        ],
        'tigo' => [
            'name' => 'Tigo Pesa',
            'prefixes' => ['71', '65', '67', '72'],
            'color' => '#FFD700',
            'icon' => 'fas fa-mobile-alt',
            'network_code' => 'TZ-TIGO-C2B'
        ],
        'halotel' => [
            'name' => 'HaloPesa',
            'prefixes' => ['62', '63'],
            'color' => '#0066CC',
            'icon' => 'fas fa-mobile-alt',
            'network_code' => 'TZ-HALOPESA-C2B'
        ],
        'ttcl' => [
            'name' => 'TTCL',
            'prefixes' => ['73'],
            'color' => '#FF6600',
            'icon' => 'fas fa-mobile-alt',
            'network_code' => 'TZ-TTCL-C2B'
        ],
        'zantel' => [
            'name' => 'Zantel',
            'prefixes' => ['77'],
            'color' => '#00CC00',
            'icon' => 'fas fa-mobile-alt',
            'network_code' => 'TZ-ZANTEL-C2B'
        ]
    ];

    /**
     * Detect network provider from phone number
     */
    public function detectNetwork(string $phoneNumber): ?array
    {
        // Clean the phone number
        $cleanNumber = $this->cleanPhoneNumber($phoneNumber);
        
        // Extract the prefix (digits after 255)
        if (preg_match('/^255(\d{2})/', $cleanNumber, $matches)) {
            $prefix = $matches[1];
            
            // Find matching network
            foreach (self::NETWORK_PREFIXES as $networkKey => $network) {
                if (in_array($prefix, $network['prefixes'])) {
                    return [
                        'key' => $networkKey,
                        'name' => $network['name'],
                        'color' => $network['color'],
                        'icon' => $network['icon'],
                        'network_code' => $network['network_code'],
                        'prefix' => $prefix
                    ];
                }
            }
        }
        
        return null;
    }

    /**
     * Get all available networks
     */
    public function getAllNetworks(): array
    {
        return self::NETWORK_PREFIXES;
    }

    /**
     * Get network by key
     */
    public function getNetworkByKey(string $key): ?array
    {
        return self::NETWORK_PREFIXES[$key] ?? null;
    }

    /**
     * Get network by network code
     */
    public function getNetworkByCode(string $networkCode): ?array
    {
        foreach (self::NETWORK_PREFIXES as $key => $network) {
            if ($network['network_code'] === $networkCode) {
                return array_merge(['key' => $key], $network);
            }
        }
        
        return null;
    }

    /**
     * Validate Tanzanian phone number format
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        $cleanNumber = $this->cleanPhoneNumber($phoneNumber);
        
        // Tanzanian format: 255 + 9 digits
        return preg_match('/^255[0-9]{9}$/', $cleanNumber);
    }

    /**
     * Clean phone number (remove spaces, dashes, etc.)
     */
    public function cleanPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Handle numbers starting with 0 (convert to 255)
        if (preg_match('/^0/', $cleaned)) {
            $cleaned = '255' . substr($cleaned, 1);
        }
        
        // Handle numbers starting with +255
        if (preg_match('/^\+255/', $phoneNumber)) {
            $cleaned = '255' . substr($cleaned, 3);
        }
        
        return $cleaned;
    }

    /**
     * Format phone number for display
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        $cleanNumber = $this->cleanPhoneNumber($phoneNumber);
        
        if (preg_match('/^255(\d{3})(\d{3})(\d{3})$/', $cleanNumber, $matches)) {
            return '+255 ' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
        }
        
        return $phoneNumber;
    }

    /**
     * Get network suggestions based on partial phone number
     */
    public function getNetworkSuggestions(string $partialNumber): array
    {
        $cleanNumber = $this->cleanPhoneNumber($partialNumber);
        $suggestions = [];
        
        // If we have at least 255 + 2 digits, try to detect
        if (preg_match('/^255(\d{2})/', $cleanNumber, $matches)) {
            $prefix = $matches[1];
            
            foreach (self::NETWORK_PREFIXES as $networkKey => $network) {
                if (in_array($prefix, $network['prefixes'])) {
                    $suggestions[] = [
                        'key' => $networkKey,
                        'name' => $network['name'],
                        'color' => $network['color'],
                        'icon' => $network['icon'],
                        'network_code' => $network['network_code'],
                        'confidence' => 'high'
                    ];
                }
            }
        }
        
        return $suggestions;
    }

    /**
     * Get network statistics (number of prefixes per network)
     */
    public function getNetworkStatistics(): array
    {
        $stats = [];
        
        foreach (self::NETWORK_PREFIXES as $key => $network) {
            $stats[$key] = [
                'name' => $network['name'],
                'prefix_count' => count($network['prefixes']),
                'prefixes' => $network['prefixes'],
                'network_code' => $network['network_code']
            ];
        }
        
        return $stats;
    }
} 