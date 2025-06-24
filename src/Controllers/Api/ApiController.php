<?php

namespace Azuriom\Plugin\ServerMonitoring\Controllers\Api;

use Azuriom\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    /**
     * API homepage - Return server status
     */
    public function index(Request $request)
    {
        return $this->getServerStatus($request);
    }
    
    /**
     * Get server status from the launcher
     */
    public function getServerStatus(Request $request)
    {
        try {
            // Check if we should use test settings
            $useTestSettings = $request->has('use_test_settings');
            $params = $useTestSettings ? ['use_test_settings' => true] : [];
            
            $response = $this->makeApiRequest('status', 'GET', $params);
            
            // Cache the server status for 30 seconds (but not test responses)
            if (!$useTestSettings) {
                Cache::put('server_monitoring_status', $response, now()->addSeconds(30));
            }
            
            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('Server monitoring API error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Could not connect to the server launcher: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Start a server or all servers
     */
    public function startServer(Request $request)
    {
        $serverName = $request->input('server');
        
        try {
            $response = $this->makeApiRequest('start', 'POST', ['server' => $serverName]);
            
            return response()->json([
                'success' => true,
                'message' => $serverName ? "Starting server {$serverName}" : "Starting all servers",
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('Server monitoring API error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start server: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Stop a server or all servers
     */
    public function stopServer(Request $request)
    {
        $serverName = $request->input('server');
        
        try {
            $response = $this->makeApiRequest('stop', 'POST', ['server' => $serverName]);
            
            return response()->json([
                'success' => true,
                'message' => $serverName ? "Stopping server {$serverName}" : "Stopping all servers",
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('Server monitoring API error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop server: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Restart a server or all servers
     */
    public function restartServer(Request $request)
    {
        $serverName = $request->input('server');
        
        try {
            $response = $this->makeApiRequest('restart', 'POST', ['server' => $serverName]);
            
            return response()->json([
                'success' => true,
                'message' => $serverName ? "Restarting server {$serverName}" : "Restarting all servers",
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('Server monitoring API error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to restart server: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get server logs
     */
    public function getServerLogs(Request $request)
    {
        $serverName = $request->input('server');
        
        if (!$serverName) {
            return response()->json([
                'success' => false,
                'message' => 'Server name is required',
            ], 400);
        }
        
        try {
            $response = $this->makeApiRequest('logs', 'GET', ['server' => $serverName]);
            
            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('Server monitoring API error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get server logs: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Make an API request to the server launcher
     */
    protected function makeApiRequest($endpoint, $method = 'GET', array $params = [])
    {
        $defaultSettings = [
            'host' => '127.0.0.1',
            'port' => 8080,
            'api_key' => '',
        ];
        
        // Check if we should use test settings
        $useTestSettings = isset($params['use_test_settings']) && $params['use_test_settings'];
        $settingKey = $useTestSettings ? 'servermonitoring.connection.test' : 'servermonitoring.connection';
        
        // Get settings from database
        $settingsJson = setting($settingKey);
        
        // Log the settings JSON for debugging
        Log::debug('Server monitoring settings JSON: ' . $settingsJson);
        
        if ($settingsJson) {
            try {
                // Try to decode the JSON string
                $settings = json_decode($settingsJson, true);
                
                // If not an array or decode failed, use default
                if (!is_array($settings)) {
                    Log::warning('Server monitoring settings is not an array after JSON decode');
                    $settings = $defaultSettings;
                }
            } catch (\Exception $e) {
                Log::error('Server monitoring settings JSON decode error: ' . $e->getMessage());
                $settings = $defaultSettings;
            }
        } else {
            Log::warning('Server monitoring settings not found in database');
            $settings = $defaultSettings;
        }
        
        $host = $settings['host'] ?? '127.0.0.1';
        $port = $settings['port'] ?? 8080;
        $apiKey = $settings['api_key'] ?? '';
        
        // Log the connection details for debugging
        Log::debug('Server monitoring connection details:', [
            'host' => $host,
            'port' => $port,
            'api_key_length' => strlen($apiKey),
            'endpoint' => $endpoint,
            'method' => $method
        ]);
        
        if (empty($host) || empty($apiKey)) {
            throw new \Exception('Server launcher API settings are not configured');
        }
        
        // Try the URL format without the 'serverlauncher/' prefix
        $url = "http://{$host}:{$port}/{$endpoint}";
        
        // Log the URL for debugging
        Log::debug('Server monitoring API request URL: ' . $url);
        
        // Add API key to the request
        $request = Http::withHeaders([
            'X-API-Key' => $apiKey,
        ]);
        
        // Add API key to query parameters as a fallback
        $params['api_key'] = $apiKey;
        
        // Remove the test settings flag from the params
        if (isset($params['use_test_settings'])) {
            unset($params['use_test_settings']);
        }
        
        // Log the request parameters for debugging
        Log::debug('Server monitoring API request params:', $params);
        
        try {
            if ($method === 'GET') {
                $response = $request->get($url, $params);
            } elseif ($method === 'POST') {
                $response = $request->post($url, $params);
            } else {
                $response = $request->get($url, $params);
            }
            
            // Log the response status and body for debugging
            Log::debug('Server monitoring API response status: ' . $response->status());
            Log::debug('Server monitoring API response body: ' . $response->body());
            
            if ($response->successful()) {
                $responseData = $response->json() ?? $response->body();
                
                // Log the parsed response data for debugging
                Log::debug('Server monitoring API parsed response:', ['data' => is_array($responseData) ? $responseData : 'non-array response']);
                
                return $responseData;
            }
            
            throw new \Exception('API request failed with status: ' . $response->status() . ' - ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Server monitoring API request error: ' . $e->getMessage());
            throw $e;
        }
    }
}
