@extends('admin.layouts.admin')

@section('title', 'Server Monitoring')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Server Monitoring Configuration</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('servermonitoring.admin.settings.update') }}" method="POST">
                @csrf

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Configure the connection to your server launcher application.
                </div>

                <div class="mb-3">
                    <label class="form-label" for="hostInput">Host</label>
                    <input type="text" class="form-control @error('host') is-invalid @enderror" id="hostInput"
                           name="host" value="{{ old('host', $host) }}" required>

                    @error('host')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <div class="form-text">The hostname or IP address of the server running the launcher application.</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" for="portInput">Port</label>
                    <input type="number" class="form-control @error('port') is-invalid @enderror" id="portInput"
                           name="port" min="1" max="65535" value="{{ old('port', $port) }}" required>

                    @error('port')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <div class="form-text">The port configured in the launcher application (default: 8080).</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="apiKeyInput">API Key</label>
                    <input type="text" class="form-control @error('api_key') is-invalid @enderror" id="apiKeyInput"
                           name="api_key" value="{{ old('api_key', $api_key) }}" required>

                    @error('api_key')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <div class="form-text">The API key generated in the launcher application's API settings.</div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Settings
                </button>
                <button type="button" id="testConnection" class="btn btn-info ms-2">
                    <i class="bi bi-link"></i> Test Connection
                </button>
            </form>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Instructions</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h6>Configuring the Server Launcher</h6>
                <ol>
                    <li>Open the Server Launcher application on your server</li>
                    <li>Click the "Settings" button and go to the "API Settings" tab</li>
                    <li>Enable the API</li>
                    <li>Generate an API key by clicking the "Generate New Key" button</li>
                    <li>Copy the generated API key and paste it in the settings above</li>
                    <li>Configure the API port (default is 8080)</li>
                    <li>Add this server's IP address to the whitelist</li>
                    <li>Click "Save" to apply the settings</li>
                </ol>
            </div>
            
            <div class="mb-3">
                <h6>Using the Server Monitoring Plugin</h6>
                <ol>
                    <li>Configure the connection settings above</li>
                    <li>Click the button below to access the server monitoring dashboard</li>
                </ol>
                
                <div class="mt-3">
                    <a href="{{ route('servermonitoring.admin.monitor') }}" class="btn btn-primary">
                        <i class="bi bi-display"></i> Open Server Monitoring Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testBtn = document.getElementById('testConnection');
            
            testBtn.addEventListener('click', function() {
                const host = document.getElementById('hostInput').value;
                const port = document.getElementById('portInput').value;
                const apiKey = document.getElementById('apiKeyInput').value;
                
                if (!host || !port || !apiKey) {
                    alert('Please fill in all connection settings before testing.');
                    return;
                }
                
                testBtn.disabled = true;
                testBtn.innerHTML = '<i class="bi bi-hourglass"></i> Testing...';
                
                fetch('{{ route('servermonitoring.admin.test-connection') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        host, port, api_key: apiKey
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Connection successful: ' + data.message);
                    } else {
                        alert('Connection failed: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error testing connection: ' + error);
                })
                .finally(() => {
                    testBtn.disabled = false;
                    testBtn.innerHTML = '<i class="bi bi-link"></i> Test Connection';
                });
            });
        });
    </script>
@endpush
