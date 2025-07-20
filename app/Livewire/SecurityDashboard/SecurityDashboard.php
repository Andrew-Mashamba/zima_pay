<?php

namespace App\Livewire\SecurityDashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SecurityDashboard extends Component
{
    public $securityStats = [];
    public $recentIncidents = [];
    public $threatAnalytics = [];
    public $activeAlerts = [];

    public function mount()
    {
        $this->loadSecurityStats();
        $this->loadRecentIncidents();
        $this->loadThreatAnalytics();
        $this->loadActiveAlerts();
    }

    public function loadSecurityStats()
    {
        $this->securityStats = [
            'total_security_events' => DB::table('security_logs')->count(),
            'critical_incidents' => DB::table('security_incidents')
                ->where('severity', 'critical')
                ->where('status', '!=', 'resolved')
                ->count(),
            'blocked_ips' => DB::table('ip_blacklist')->count(),
            'failed_authentications_today' => DB::table('failed_authentications')
                ->whereDate('created_at', today())
                ->count(),
            'active_threats' => DB::table('security_incidents')
                ->where('status', 'open')
                ->count(),
            'rate_limit_violations_today' => DB::table('security_logs')
                ->where('event_type', 'rate_limit_violation')
                ->whereDate('created_at', today())
                ->count(),
        ];
    }

    public function loadRecentIncidents()
    {
        $this->recentIncidents = DB::table('security_incidents')
            ->select('id', 'incident_type', 'severity', 'title', 'source_ip', 'status', 'detected_at')
            ->orderBy('detected_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function loadThreatAnalytics()
    {
        $last24Hours = now()->subDay();
        
        $this->threatAnalytics = [
            'threat_types' => DB::table('security_logs')
                ->select('event_type', DB::raw('count(*) as count'))
                ->where('created_at', '>=', $last24Hours)
                ->where('event_type', 'LIKE', 'threat_%')
                ->groupBy('event_type')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get()
                ->toArray(),
            'hourly_activity' => DB::table('security_logs')
                ->select(DB::raw('EXTRACT(HOUR FROM created_at) as hour'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $last24Hours)
                ->groupBy(DB::raw('EXTRACT(HOUR FROM created_at)'))
                ->orderBy('hour')
                ->get()
                ->toArray(),
        ];
    }

    public function loadActiveAlerts()
    {
        $this->activeAlerts = [
            [
                'id' => 1,
                'type' => 'High Rate Limit Violations',
                'message' => 'IP 192.168.1.100 exceeded rate limits 50 times in the last hour',
                'severity' => 'high',
                'timestamp' => now()->subMinutes(15)
            ],
            [
                'id' => 2,
                'type' => 'Suspicious User Agent',
                'message' => 'Multiple requests from scanner-like user agents detected',
                'severity' => 'medium',
                'timestamp' => now()->subMinutes(30)
            ],
            [
                'id' => 3,
                'type' => 'Failed Authentication Spike',
                'message' => '25 failed authentication attempts in 10 minutes',
                'severity' => 'high',
                'timestamp' => now()->subHour()
            ]
        ];
    }

    public function resolveAlert($alertId)
    {
        // Remove alert from active alerts
        $this->activeAlerts = collect($this->activeAlerts)
            ->reject(function ($alert) use ($alertId) {
                return $alert['id'] == $alertId;
            })
            ->values()
            ->toArray();

        session()->flash('message', 'Alert resolved successfully.');
    }

    public function blockIp($ip)
    {
        DB::table('ip_blacklist')->insert([
            'ip_address' => $ip,
            'reason' => 'Manual block from security dashboard',
            'is_permanent' => false,
            'expires_at' => now()->addHours(24),
            'added_by' => auth()->user()->email,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        session()->flash('message', "IP {$ip} has been blocked successfully.");
        $this->loadSecurityStats();
    }

    public function render()
    {
        return view('livewire.security-dashboard.security-dashboard');
    }
}