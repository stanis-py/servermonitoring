# Server Monitoring Plugin for Azuriom

A comprehensive server monitoring plugin for Azuriom that allows administrators to monitor, manage, and control game servers from a central dashboard.

## Features

- **Real-time Server Monitoring:** Track server status, uptime, and performance metrics
- **Server Management:** Start, stop, and restart servers remotely
- **Log Viewer:** Access server logs directly from the admin panel
- **Auto-restart Configuration:** View which servers are configured for automatic restart
- **API Integration:** Connect with server launcher applications to control server processes
- **Admin Dashboard:** Intuitive interface for monitoring all your servers at once

## Integration with ServerStarter

This plugin is designed to work seamlessly with [ServerStarter](https://github.com/stanis-py/ServerStarter), a robust Windows application for managing multiple server processes. The integration provides:

- Remote management of your servers through the Azuriom CMS web interface
- Real-time monitoring of server status
- Access to server logs from the CMS dashboard
- Ability to start, stop, and restart servers remotely
- Auto-restart configuration management

The plugin communicates with ServerStarter via its REST API to provide these features.

## Requirements

- Azuriom CMS
- PHP 8.0 or higher
- Running [ServerStarter](https://github.com/stanis-py/ServerStarter) application with API access enabled

## Installation

1. Download the plugin from the [GitHub repository](https://github.com/stanis-py/servermonitoring)
2. Install it through your Azuriom admin panel (or extract to the `plugins` directory)
3. Enable the plugin in the admin panel
4. Configure connection settings to your ServerStarter instance

## Configuration

Navigate to the plugin's admin panel to configure:

1. ServerStarter Host (default: 127.0.0.1)
2. API Port (default: 8080)
3. API Key (required for authentication)

You can test the connection to verify your configuration is working properly.

## Usage

### Monitoring Servers

The main dashboard displays all your servers with their:
- Current status (running/stopped)
- Runtime information
- Auto-restart configuration
- Management buttons

### Managing Servers

For each server, you can:
- Start servers that are stopped
- Stop running servers
- Restart active servers
- View server logs

You can also perform bulk actions on all servers at once.

### API Integration

The plugin communicates with ServerStarter through a REST API, supporting these endpoints:

- `/status` - Get server status information
- `/start` - Start a server or all servers
- `/stop` - Stop a server or all servers
- `/restart` - Restart a server or all servers
- `/logs` - View server logs

## Directory Structure

- `assets/`: CSS and JavaScript files
- `database/migrations/`: Database migration files
- `resources/views/`: Blade template views
- `routes/`: Route definitions (web, api, admin)
- `src/`: Core application code
  - `Controllers/`: Request handling logic
  - `Models/`: Database models
  - `Providers/`: Service providers for Azuriom integration

## License

MIT License

Copyright (c) 2023 stanis-py

## Support

For issues, feature requests, or questions, please open an issue in the [GitHub repository](https://github.com/stanis-py/servermonitoring/issues).
