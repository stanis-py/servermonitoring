@extends('admin.layouts.admin')

@section('title', 'Server Logs - ' . $serverName)

@section('content')
    <div class="container content">
        <div class="card shadow mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Server Logs: {{ $serverName }}</h3>
                    <a href="{{ route('servermonitoring.admin.monitor') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Server Status
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if ($connectionError)
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Connection Error: 
                        {{ $errorMessage ?? 'Could not retrieve logs from the server launcher.' }}
                    </div>
                @elseif (count($logs) === 0)
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No logs available for this server.
                    </div>
                @else
                    <div class="logs-container bg-dark text-light p-3 rounded" style="max-height: 600px; overflow-y: auto; font-family: monospace;">
                        @foreach ($logs as $log)
                            <div class="log-line">{{ $log }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .logs-container {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log-line {
            padding: 2px 0;
            border-bottom: 1px solid #333;
        }
    </style>
@endpush 