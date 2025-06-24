<?php

namespace Azuriom\Plugin\ServerMonitoring\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Show the home admin page.
     */
    public function index()
    {
        $defaultSettings = [
            'host' => '127.0.0.1',
            'port' => 8080,
            'api_key' => '',
        ];
        
        $settingsJson = setting('servermonitoring.connection');
        
        // Log the settings for debugging
        Log::debug('Admin - Server monitoring settings JSON: ' . $settingsJson);
        
        if ($settingsJson) {
            try {
                // Try to decode the JSON string
                $settings = json_decode($settingsJson, true);
                
                // If not an array or decode failed, use default
                if (!is_array($settings)) {
                    Log::warning('Admin - Server monitoring settings is not an array after JSON decode');
                    $settings = $defaultSettings;
                }
            } catch (\Exception $e) {
                Log::error('Admin - Server monitoring settings JSON decode error: ' . $e->getMessage());
                $settings = $defaultSettings;
            }
        } else {
            Log::info('Admin - No server monitoring settings found, using defaults');
            $settings = $defaultSettings;
        }
        
        return view('servermonitoring::admin.index', [
            'host' => $settings['host'] ?? '127.0.0.1',
            'port' => $settings['port'] ?? 8080,
            'api_key' => $settings['api_key'] ?? '',
            'monitoringUrl' => route('servermonitoring.admin.monitor')
        ]);
    }
    
    /**
     * Update plugin settings
     */
    public function update(Request $request)
    {
        $validated = $this->validate($request, [
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'api_key' => ['required', 'string', 'max:255'],
        ]);
        
        // Create settings array
        $connectionSettings = [
            'host' => $validated['host'],
            'port' => (int) $validated['port'],
            'api_key' => $validated['api_key'],
        ];
        
        // Log the settings being saved
        Log::info('Admin - Saving server monitoring settings', [
            'host' => $connectionSettings['host'],
            'port' => $connectionSettings['port'],
            'api_key_length' => strlen($connectionSettings['api_key'])
        ]);
        
        // Convert array to JSON string before saving
        $jsonSettings = json_encode($connectionSettings);
        
        // Verify JSON encoding worked
        if ($jsonSettings === false) {
            Log::error('Admin - Failed to JSON encode server monitoring settings');
            return redirect()->route('servermonitoring.admin.index')
                ->with('error', 'Failed to save settings: JSON encoding error');
        }
        
        Setting::updateSettings('servermonitoring.connection', $jsonSettings);
        
        // Verify settings were saved correctly
        $savedSettings = setting('servermonitoring.connection');
        if ($savedSettings !== $jsonSettings) {
            Log::warning('Admin - Saved settings do not match the input settings', [
                'input' => $jsonSettings,
                'saved' => $savedSettings
            ]);
        }
        
        return redirect()->route('servermonitoring.admin.index')
            ->with('success', 'Server monitoring settings updated successfully.');
    }
    
    /**
     * Test the connection to the launcher
     */
    public function testConnection(Request $request)
    {
        try {
            // Create a temporary connection setting for testing
            $connectionSettings = [
                'host' => $request->input('host'),
                'port' => (int) $request->input('port'),
                'api_key' => $request->input('api_key'),
            ];
            
            // Log the test connection attempt
            Log::info('Admin - Testing server monitoring connection', [
                'host' => $connectionSettings['host'],
                'port' => $connectionSettings['port'],
                'api_key_length' => strlen($connectionSettings['api_key'])
            ]);
            
            // Temporarily store the test settings
            $jsonSettings = json_encode($connectionSettings);
            Setting::updateSettings('servermonitoring.connection.test', $jsonSettings);
            
            $apiController = new \Azuriom\Plugin\ServerMonitoring\Controllers\Api\ApiController();
            
            // Create a new request with the test flag
            $testRequest = Request::create('/', 'GET', ['use_test_settings' => true]);
            $response = $apiController->getServerStatus($testRequest);
            
            // Clean up temporary test settings
            Setting::updateSettings('servermonitoring.connection.test', null);
            
            // Log the test response
            $responseData = json_decode($response->getContent(), true);
            Log::info('Admin - Test connection response', [
                'status_code' => $response->getStatusCode(),
                'success' => $responseData['success'] ?? false
            ]);
            
            if ($response->getStatusCode() === 200) {
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully connected to the server launcher',
                    'data' => $responseData['data'] ?? null
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to server launcher: Invalid response',
                'error' => $responseData['message'] ?? 'Unknown error'
            ], 500);
        } catch (\Exception $e) {
            // Clean up temporary test settings if there was an error
            Setting::updateSettings('servermonitoring.connection.test', null);
            
            // Log the exception
            Log::error('Admin - Test connection exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to server launcher: ' . $e->getMessage(),
            ], 500);
        }
    }
}
