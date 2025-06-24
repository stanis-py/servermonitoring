@extends('admin.layouts.admin')

@section('title', 'Server Monitoring')

@section('content')
    <div class="container content">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">Server Status</h3>
            </div>
            <div class="card-body">
                @if ($connectionError)
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Connection Error: 
                        {{ $errorMessage ?? 'Could not connect to the server launcher. Please make sure it is running and properly configured.' }}
                    </div>
                    
                    <div class="mt-3">
                        <p>Please check the following:</p>
                        <ul>
                            <li>The server launcher application is running</li>
                            <li>The API is enabled in the server launcher settings</li>
                            <li>The API key is correctly configured</li>
                            <li>The host and port settings are correct</li>
                            <li>There are no firewall restrictions blocking the connection</li>
                        </ul>
                        
                        <p>You can configure the connection settings in the <a href="{{ route('servermonitoring.admin.index') }}">admin panel</a>.</p>
                    </div>
                @elseif (count($servers) === 0)
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> No servers found.
                    </div>
                    
                    <div class="mt-3">
                        <p>No servers were found in the launcher. This could be because:</p>
                        <ul>
                            <li>No servers are configured in the launcher</li>
                            <li>The launcher API is not returning server data correctly</li>
                        </ul>
                        
                        <p>Please check your server launcher configuration and make sure servers are properly set up.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Server</th>
                                    <th>Status</th>
                                    <th>Runtime</th>
                                    <th>Auto Restart</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($servers as $server)
                                    <tr>
                                        <td>{{ $server['name'] ?? 'Unknown' }}</td>
                                        <td>
                                            @if (isset($server['status']) && strtolower($server['status']) === 'running')
                                                <span class="badge bg-success">Running</span>
                                            @else
                                                <span class="badge bg-danger">Stopped</span>
                                            @endif
                                        </td>
                                        <td>{{ $server['runtime'] ?? 'N/A' }}</td>
                                        <td>
                                            @if (isset($server['autoRestart']) && $server['autoRestart'])
                                                <span class="badge bg-info">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if (isset($server['status']) && strtolower($server['status']) === 'running')
                                                    <button type="button" class="btn btn-sm btn-danger server-action" 
                                                        data-action="stop" data-server="{{ $server['name'] ?? 'Unknown' }}">
                                                        <i class="bi bi-stop-fill"></i> Stop
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning server-action" 
                                                        data-action="restart" data-server="{{ $server['name'] ?? 'Unknown' }}">
                                                        <i class="bi bi-arrow-repeat"></i> Restart
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-success server-action" 
                                                        data-action="start" data-server="{{ $server['name'] ?? 'Unknown' }}">
                                                        <i class="bi bi-play-fill"></i> Start
                                                    </button>
                                                @endif
                                                <a href="{{ route('servermonitoring.admin.logs', ['server' => $server['name'] ?? 'Unknown']) }}" 
                                                    class="btn btn-sm btn-secondary">
                                                    <i class="bi bi-file-text"></i> Logs
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success server-action" data-action="start">
                                <i class="bi bi-play-fill"></i> Start All
                            </button>
                            <button type="button" class="btn btn-danger server-action" data-action="stop">
                                <i class="bi bi-stop-fill"></i> Stop All
                            </button>
                            <button type="button" class="btn btn-warning server-action" data-action="restart">
                                <i class="bi bi-arrow-repeat"></i> Restart All
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.server-action').forEach(function(button) {
                button.addEventListener('click', function() {
                    const action = this.dataset.action;
                    const server = this.dataset.server || null;
                    const actionTitle = {
                        'start': 'Start',
                        'stop': 'Stop',
                        'restart': 'Restart'
                    };
                    
                    const title = actionTitle[action] + (server ? ` ${server}` : ' all servers');
                    
                    if (!confirm(`Are you sure you want to ${action} ${server || 'all servers'}?`)) {
                        return;
                    }
                    
                    button.disabled = true;
                    button.innerHTML = '<i class="bi bi-hourglass"></i> Processing...';
                    
                    // Send AJAX request
                    const formData = new FormData();
                    if (server) {
                        formData.append('server', server);
                    }
                    
                    // Add CSRF token to form data
                    formData.append('_token', '{{ csrf_token() }}');
                    
                    fetch('{{ route('servermonitoring.api.start') }}'.replace('start', action), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert(data.message || 'Operation completed successfully. The page will refresh.');
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Unknown error occurred'));
                            button.disabled = false;
                            button.innerHTML = getButtonHTML(action);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error: ' + error.message);
                        button.disabled = false;
                        button.innerHTML = getButtonHTML(action);
                    });
                });
            });
            
            // Helper function to get button HTML based on action
            function getButtonHTML(action) {
                const icons = {
                    'start': 'bi-play-fill',
                    'stop': 'bi-stop-fill',
                    'restart': 'bi-arrow-repeat'
                };
                
                const labels = {
                    'start': 'Start',
                    'stop': 'Stop',
                    'restart': 'Restart'
                };
                
                return `<i class="bi ${icons[action]}"></i> ${labels[action]}`;
            }
        });
    </script>
@endpush
