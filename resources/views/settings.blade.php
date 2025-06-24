@extends('layouts.app')

@section('title', 'Server Settings')

@section('content')
    <div class="container content">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">Server Settings</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <a href="{{ route('servermonitoring.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Server Status
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Configure your server preferences. These settings are stored in your browser and apply only to this device.
                </div>

                <form id="settingsForm">
                    <div class="mb-3">
                        <label class="form-label" for="refreshInterval">Auto-refresh Interval (seconds)</label>
                        <input type="number" class="form-control" id="refreshInterval" 
                               min="5" max="300" value="30">
                        <div class="form-text">How often the server status should automatically refresh (5-300 seconds).</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enableNotifications" checked>
                            <label class="form-check-label" for="enableNotifications">Enable Browser Notifications</label>
                        </div>
                        <div class="form-text">Receive notifications when server status changes.</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="darkThemeConsole" checked>
                            <label class="form-check-label" for="darkThemeConsole">Use Dark Theme for Console</label>
                        </div>
                        <div class="form-text">Display server console logs with a dark background.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="logFontSize">Console Font Size (px)</label>
                        <input type="number" class="form-control" id="logFontSize" 
                               min="10" max="18" value="12">
                        <div class="form-text">The font size for console log text (10-18px).</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Settings
                    </button>
                    <button type="button" id="resetSettings" class="btn btn-danger">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset to Default
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const settingsForm = document.getElementById('settingsForm');
            const resetButton = document.getElementById('resetSettings');
            
            // Default settings
            const defaultSettings = {
                refreshInterval: 30,
                enableNotifications: true,
                darkThemeConsole: true,
                logFontSize: 12
            };
            
            // Load settings from localStorage
            function loadSettings() {
                let settings = localStorage.getItem('serverMonitoringSettings');
                if (settings) {
                    settings = JSON.parse(settings);
                } else {
                    settings = defaultSettings;
                    saveSettings(settings);
                }
                
                // Apply settings to form
                document.getElementById('refreshInterval').value = settings.refreshInterval;
                document.getElementById('enableNotifications').checked = settings.enableNotifications;
                document.getElementById('darkThemeConsole').checked = settings.darkThemeConsole;
                document.getElementById('logFontSize').value = settings.logFontSize;
                
                return settings;
            }
            
            // Save settings to localStorage
            function saveSettings(settings) {
                localStorage.setItem('serverMonitoringSettings', JSON.stringify(settings));
            }
            
            // Handle form submission
            settingsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const settings = {
                    refreshInterval: parseInt(document.getElementById('refreshInterval').value, 10),
                    enableNotifications: document.getElementById('enableNotifications').checked,
                    darkThemeConsole: document.getElementById('darkThemeConsole').checked,
                    logFontSize: parseInt(document.getElementById('logFontSize').value, 10)
                };
                
                // Validate settings
                if (settings.refreshInterval < 5) settings.refreshInterval = 5;
                if (settings.refreshInterval > 300) settings.refreshInterval = 300;
                if (settings.logFontSize < 10) settings.logFontSize = 10;
                if (settings.logFontSize > 18) settings.logFontSize = 18;
                
                saveSettings(settings);
                
                alert('Settings saved successfully!');
            });
            
            // Handle reset button
            resetButton.addEventListener('click', function() {
                if (confirm('Are you sure you want to reset all settings to default?')) {
                    saveSettings(defaultSettings);
                    loadSettings();
                    alert('Settings have been reset to default values.');
                }
            });
            
            // Initialize settings
            loadSettings();
            
            // Request notification permission if enabled
            if (document.getElementById('enableNotifications').checked) {
                if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
                    Notification.requestPermission();
                }
            }
        });
    </script>
@endpush 