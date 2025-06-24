<?php

namespace Azuriom\Plugin\ServerMonitoring\Controllers;

use Azuriom\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Azuriom\Plugin\ServerMonitoring\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServerMonitoringHomeController extends Controller
{
    /**
     * Show the home plugin page with server status.
     */
    public function index()
    {
        try {
            // Clear the cache to ensure we get fresh data
            $this->clearCache();
            
            // Try to get server status from cache
            $serverStatus = Cache::get('server_monitoring_status');
            
            // Log cache status
            Log::debug('Server monitoring cache status: ' . ($serverStatus ? 'hit' : 'miss'));
            
            // If not in cache, try to fetch it from the API
            if (!$serverStatus) {
                $apiController = new ApiController();
                $response = $apiController->getServerStatus(request());
                
                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    $serverStatus = $data['data'] ?? null;
                    
                    // Log the API response data
                    Log::debug('Server monitoring API response data:', [
                        'status_code' => $response->getStatusCode(),
                        'has_data' => isset($data['data']),
                        'server_status' => $serverStatus ? 'not empty' : 'empty'
                    ]);
                } else {
                    // Log the API error response
                    Log::error('Server monitoring API error response:', [
                        'status_code' => $response->getStatusCode(),
                        'content' => $response->getContent()
                    ]);
                }
            }
            
            // Process the server status data to ensure it's in the expected format
            $servers = [];
            
            if (!empty($serverStatus)) {
                // From the logs, we can see the structure is: data -> success -> data -> servers
                // Check if the response has a 'success' field and nested data structure
                if (isset($serverStatus['success']) && $serverStatus['success'] === true && isset($serverStatus['data']['servers'])) {
                    $servers = $serverStatus['data']['servers'];
                }
                // Check if the response has a 'data.servers' structure
                else if (isset($serverStatus['data']) && isset($serverStatus['data']['servers'])) {
                    $servers = $serverStatus['data']['servers'];
                }
                // Check if the response has a 'servers' key directly
                else if (isset($serverStatus['servers'])) {
                    $servers = $serverStatus['servers'];
                }
                // Check if the response itself is an array of servers
                else if (is_array($serverStatus)) {
                    // If the response is a numeric array, assume it's an array of servers
                    if (isset($serverStatus[0])) {
                        $servers = $serverStatus;
                    } 
                    // If it's an associative array with server properties, assume it's a single server
                    else if (isset($serverStatus['name']) || isset($serverStatus['status'])) {
                        $servers = [$serverStatus];
                    }
                }
            }
            
            $connectionError = empty($servers);
            
            // Log the processed server data
            Log::debug('Server monitoring processed data:', [
                'connection_error' => $connectionError,
                'servers_count' => count($servers),
                'servers' => json_encode($servers)
            ]);
            
            return view('servermonitoring::index', [
                'servers' => $servers,
                'connectionError' => $connectionError,
            ]);
        } catch (\Exception $e) {
            // Log any exceptions
            Log::error('Server monitoring exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('servermonitoring::index', [
                'servers' => [],
                'connectionError' => true,
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Show the logs for a specific server
     */
    public function logs($server)
    {
        try {
            $apiController = new ApiController();
            $response = $apiController->getServerLogs(request()->merge(['server' => $server]));
            
            $logs = [];
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getContent(), true);
                $logs = $data['data']['logs'] ?? [];
            }
            
            return view('servermonitoring::logs', [
                'serverName' => $server,
                'logs' => $logs,
                'connectionError' => empty($logs),
            ]);
        } catch (\Exception $e) {
            return view('servermonitoring::logs', [
                'serverName' => $server,
                'logs' => [],
                'connectionError' => true,
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Show the settings page
     */
    public function settings()
    {
        return view('servermonitoring::settings');
    }
    
    /**
     * Get server status - AJAX endpoint
     */
    public function getServerStatus(Request $request)
    {
        $apiController = new ApiController();
        return $apiController->getServerStatus($request);
    }
    
    /**
     * Start a server - AJAX endpoint
     */
    public function startServer(Request $request)
    {
        $apiController = new ApiController();
        return $apiController->startServer($request);
    }
    
    /**
     * Stop a server - AJAX endpoint
     */
    public function stopServer(Request $request)
    {
        $apiController = new ApiController();
        return $apiController->stopServer($request);
    }
    
    /**
     * Restart a server - AJAX endpoint
     */
    public function restartServer(Request $request)
    {
        $apiController = new ApiController();
        return $apiController->restartServer($request);
    }
    
    /**
     * Get server logs - AJAX endpoint
     */
    public function getServerLogs(Request $request)
    {
        $apiController = new ApiController();
        return $apiController->getServerLogs($request);
    }
    
    /**
     * Show debug information
     */
    public function debug()
    {
        try {
            // Clear the cache to ensure we get fresh data
            $this->clearCache();
            
            $apiController = new ApiController();
            $response = $apiController->getServerStatus(request());
            
            $responseData = [
                'status_code' => $response->getStatusCode(),
                'content_type' => $response->headers->get('Content-Type'),
                'raw_content' => $response->getContent(),
            ];
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getContent(), true);
                $responseData['parsed_data'] = $data;
                
                // Process the data as in the index method
                $serverStatus = $data['data'] ?? null;
                $servers = [];
                
                if (!empty($serverStatus)) {
                    // From the logs, we can see the structure is: data -> success -> data -> servers
                    // Check if the response has a 'success' field and nested data structure
                    if (isset($serverStatus['success']) && $serverStatus['success'] === true && isset($serverStatus['data']['servers'])) {
                        $servers = $serverStatus['data']['servers'];
                    }
                    // Check if the response has a 'data.servers' structure
                    else if (isset($serverStatus['data']) && isset($serverStatus['data']['servers'])) {
                        $servers = $serverStatus['data']['servers'];
                    }
                    // Check if the response has a 'servers' key directly
                    else if (isset($serverStatus['servers'])) {
                        $servers = $serverStatus['servers'];
                    }
                    // Check if the response itself is an array of servers
                    else if (is_array($serverStatus)) {
                        // If the response is a numeric array, assume it's an array of servers
                        if (isset($serverStatus[0])) {
                            $servers = $serverStatus;
                        } 
                        // If it's an associative array with server properties, assume it's a single server
                        else if (isset($serverStatus['name']) || isset($serverStatus['status'])) {
                            $servers = [$serverStatus];
                        }
                    }
                }
                
                $responseData['processed_servers'] = $servers;
                $responseData['servers_count'] = count($servers);
            }
            
            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Clear the server status cache
     */
    private function clearCache()
    {
        try {
            Cache::forget('server_monitoring_status');
            Log::debug('Server monitoring cache cleared');
        } catch (\Exception $e) {
            Log::error('Failed to clear server monitoring cache: ' . $e->getMessage());
        }
    }
}
