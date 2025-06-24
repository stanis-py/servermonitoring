<?php

return [
    'title' => 'Server Monitoring',
    'admin' => [
        'title' => 'Server Monitoring',
        'settings' => 'Settings',
        'configuration' => 'Configuration',
        'connection' => [
            'title' => 'Connection Settings',
            'host' => 'Host',
            'port' => 'Port',
            'username' => 'Username',
            'password' => 'Password',
            'test' => 'Test Connection',
            'save' => 'Save Settings',
            'success' => 'Settings saved successfully',
            'error' => 'Error saving settings',
        ],
        'instructions' => [
            'title' => 'Instructions',
            'launcher_config' => 'Configuring the Server Launcher',
            'plugin_usage' => 'Using the Server Monitoring Plugin',
        ],
    ],
    'servers' => [
        'title' => 'Server Status',
        'name' => 'Server',
        'status' => 'Status',
        'runtime' => 'Runtime',
        'auto_restart' => 'Auto Restart',
        'actions' => 'Actions',
        'no_servers' => 'No servers found',
        'connection_error' => 'Could not connect to the server launcher',
        'start' => 'Start',
        'stop' => 'Stop',
        'restart' => 'Restart',
        'logs' => 'Logs',
        'start_all' => 'Start All',
        'stop_all' => 'Stop All',
        'restart_all' => 'Restart All',
        'confirm' => 'Are you sure you want to :action :server?',
    ],
    'logs' => [
        'title' => 'Server Logs',
        'for' => 'Logs for :server',
        'back' => 'Back to Servers',
        'no_logs' => 'No logs available for this server',
        'connection_error' => 'Could not get logs from the server launcher',
        'refresh' => 'Refresh Logs',
        'auto_refresh' => 'Auto Refresh',
        'auto_refresh_on' => 'Auto Refresh: On (10s)',
        'auto_refresh_off' => 'Auto Refresh: Off',
    ],
    'actions' => [
        'start' => [
            'success' => 'Server started successfully',
            'error' => 'Failed to start server',
        ],
        'stop' => [
            'success' => 'Server stopped successfully',
            'error' => 'Failed to stop server',
        ],
        'restart' => [
            'success' => 'Server restarted successfully',
            'error' => 'Failed to restart server',
        ],
    ],
];
