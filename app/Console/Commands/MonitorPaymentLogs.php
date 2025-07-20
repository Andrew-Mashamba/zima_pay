<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MonitorPaymentLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:monitor-payments 
                            {--channel=payments : Log channel to monitor}
                            {--lines=50 : Number of lines to show initially}
                            {--follow : Follow log file in real-time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor payment processing logs in real-time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $channel = $this->option('channel');
        $lines = $this->option('lines');
        $follow = $this->option('follow');
        
        $logFile = $this->getLogFilePath($channel);
        
        if (!File::exists($logFile)) {
            $this->error("Log file not found: {$logFile}");
            return 1;
        }
        
        $this->info("Monitoring payment logs: {$logFile}");
        $this->info("Channel: {$channel}");
        $this->info("Press Ctrl+C to stop monitoring\n");
        
        // Show initial lines
        $this->showLogLines($logFile, $lines);
        
        if ($follow) {
            $this->followLogFile($logFile);
        }
        
        return 0;
    }
    
    /**
     * Get log file path for channel
     */
    private function getLogFilePath(string $channel): string
    {
        $channelMap = [
            'payments' => 'payments-' . now()->format('Y-m-d') . '.log',
            'payment_errors' => 'payment_errors-' . now()->format('Y-m-d') . '.log',
            'aggregator_responses' => 'aggregator_responses-' . now()->format('Y-m-d') . '.log',
            'third_party_api' => 'third_party_api-' . now()->format('Y-m-d') . '.log',
            'security_events' => 'security_events-' . now()->format('Y-m-d') . '.log',
        ];
        
        $filename = $channelMap[$channel] ?? 'laravel.log';
        return storage_path("logs/{$filename}");
    }
    
    /**
     * Show log lines
     */
    private function showLogLines(string $logFile, int $lines): void
    {
        $content = File::get($logFile);
        $lines_array = explode("\n", $content);
        $lines_array = array_filter($lines_array); // Remove empty lines
        
        $start = max(0, count($lines_array) - $lines);
        $relevant_lines = array_slice($lines_array, $start);
        
        foreach ($relevant_lines as $line) {
            $this->formatAndDisplayLogLine($line);
        }
    }
    
    /**
     * Follow log file in real-time
     */
    private function followLogFile(string $logFile): void
    {
        $lastSize = File::size($logFile);
        
        while (true) {
            clearstatcache();
            
            if (!File::exists($logFile)) {
                $this->error("Log file was deleted or moved");
                break;
            }
            
            $currentSize = File::size($logFile);
            
            if ($currentSize > $lastSize) {
                $newContent = File::get($logFile);
                $newLines = substr($newContent, $lastSize);
                
                if (!empty($newLines)) {
                    $lines = explode("\n", $newLines);
                    foreach ($lines as $line) {
                        if (!empty(trim($line))) {
                            $this->formatAndDisplayLogLine($line);
                        }
                    }
                }
                
                $lastSize = $currentSize;
            }
            
            usleep(500000); // Sleep for 0.5 seconds
        }
    }
    
    /**
     * Format and display log line
     */
    private function formatAndDisplayLogLine(string $line): void
    {
        // Parse JSON log line
        if (preg_match('/\[(.*?)\] (\w+)\.(\w+): (.*)/', $line, $matches)) {
            $timestamp = $matches[1];
            $environment = $matches[2];
            $level = strtoupper($matches[3]);
            $message = $matches[4];
            
            // Try to parse JSON data
            $jsonData = null;
            if (preg_match('/\{(.*)\}$/', $message, $jsonMatches)) {
                try {
                    $jsonData = json_decode('{' . $jsonMatches[1] . '}', true);
                    $message = preg_replace('/\s+\{.*\}$/', '', $message);
                } catch (\Exception $e) {
                    // JSON parsing failed, keep original message
                }
            }
            
            // Color code based on level
            $levelColor = match($level) {
                'ERROR' => 'red',
                'WARNING' => 'yellow',
                'INFO' => 'green',
                'DEBUG' => 'blue',
                default => 'white'
            };
            
            $this->line("<fg={$levelColor}>[{$timestamp}] {$level}</> {$message}");
            
            // Display JSON data if available
            if ($jsonData && $this->output->isVerbose()) {
                $this->line(json_encode($jsonData, JSON_PRETTY_PRINT));
            }
        } else {
            // Fallback for non-standard log lines
            $this->line($line);
        }
    }
} 