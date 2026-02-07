# RabbitMQ Utils

A command-line tool for managing RabbitMQ queues, shovels, and transports. Built with Laravel Zero, this utility provides an easy-to-use interface for common RabbitMQ administration tasks.

## Features

- **Queue Management**: List, create, and inspect queues and their messages
- **Shovel Management**: Create, list, and remove RabbitMQ shovels for message migration
- **Transport Configuration**: Manage multiple RabbitMQ connections via YAML configuration
- **Cross-vhost Operations**: Work with different virtual hosts easily
- **Docker Ready**: Includes Docker Compose configuration for testing

## Requirements

- PHP 8.2 or higher
- Composer
- RabbitMQ server with Management API enabled

## Installation

1. Clone the repository:
```bash
git clone https://github.com/pashamray/rabbitmq-utils.git
cd rabbitmq-utils
```

2. Install dependencies:
```bash
composer install
```

3. Configure your transports:
```bash
cp transports.yaml.example transports.yaml
```

4. Edit `transports.yaml` with your RabbitMQ connection details.

## Configuration

The `transports.yaml` file defines your RabbitMQ connections. Each transport can have two clients:

- **amqp**: For message operations (port 5672) - used for consuming/publishing messages
- **manager**: For management API operations (port 15672) - used for administrative tasks

### Basic Configuration Example

```yaml
default:
    clients:
        amqp:
            host: 'rabbitmq-first'
            vhost: '/'
            port: 5672
            login: 'guest'
            password: 'guest'
            tls: false
            tls_verify: false
        manager:
            host: 'rabbitmq-first'
            vhost: '/'
            port: 15672
            login: 'guest'
            password: 'guest'
            tls: false
            tls_verify: false
```

### Multiple Environments Example

You can configure multiple transports for different environments:

```yaml
default:
    clients:
        amqp:
            host: 'localhost'
            vhost: '/'
            port: 5672
            login: 'guest'
            password: 'guest'
            tls: false
            tls_verify: false
        manager:
            host: 'localhost'
            vhost: '/'
            port: 15672
            login: 'guest'
            password: 'guest'
            tls: false
            tls_verify: false

production:
    clients:
        amqp:
            host: 'rabbitmq.prod.example.com'
            vhost: '/prod'
            port: 5672
            login: 'prod-user'
            password: 'secure-password'
            tls: true
            tls_verify: true
        manager:
            host: 'rabbitmq.prod.example.com'
            vhost: '/prod'
            port: 15672
            login: 'prod-admin'
            password: 'secure-admin-password'
            tls: true
            tls_verify: true

staging:
    clients:
        amqp:
            host: 'rabbitmq.staging.example.com'
            vhost: '/staging'
            port: 5672
            login: 'staging-user'
            password: 'staging-password'
            tls: true
            tls_verify: false
        manager:
            host: 'rabbitmq.staging.example.com'
            vhost: '/staging'
            port: 15672
            login: 'staging-admin'
            password: 'staging-admin-password'
            tls: true
            tls_verify: false
```

### Configuration Options

- **host**: RabbitMQ server hostname or IP address
- **vhost**: Virtual host name (default is usually `/`)
- **port**: Connection port (5672 for AMQP, 15672 for Management API)
- **login**: Username for authentication
- **password**: Password for authentication
- **tls**: Enable TLS/SSL encryption (`true`/`false`)
- **tls_verify**: Verify TLS certificates (`true`/`false`)

**Note**: When using `--vhost` parameter in commands, you're specifying which vhost to work with *on* the transport you've selected. The vhost in the transport configuration is just the default connection vhost.

## Usage

All commands support the `--help` flag to display usage information:

```bash
php rabbitmq-utils queue:list --help
```

### Quick Start

1. **Make the script executable** (optional):
```bash
chmod +x rabbitmq-utils
./rabbitmq-utils queue:list
```

2. **Or run with PHP directly**:
```bash
php rabbitmq-utils queue:list
```

### Queue Commands

#### List Queues

Display all queues in a virtual host with detailed information including message counts, consumers, and queue type.

```bash
php rabbitmq-utils queue:list [--vhost=default] [--transport=default] [--name-only]
```

**Options:**
- `--vhost`: Virtual host to query (default: `default`, which refers to your configured vhost in transports.yaml)
- `--transport`: Transport configuration to use (default: `default`)
- `--name-only`: Display only queue names (useful for scripting)

**Output includes:**
- vhost: Virtual host name
- name: Queue name
- type: Queue type (classic, quorum, or stream)
- consumers: Number of active consumers
- messages: Total messages in queue
- arguments: Number of custom arguments

**Examples:**

```bash
# List all queues in the default vhost with full details
php rabbitmq-utils queue:list

# List queues in a specific vhost (using the vhost configured in transports.yaml)
php rabbitmq-utils queue:list --vhost=production

# Get only queue names (for piping to other commands)
php rabbitmq-utils queue:list --name-only

# Use a different transport configuration
php rabbitmq-utils queue:list --transport=staging

# Combine options to filter output
php rabbitmq-utils queue:list --vhost=production --name-only
```

#### Create a Queue

Create a new queue with specified properties.

```bash
php rabbitmq-utils queue:create <queue-name> \
    [--durable=true] \
    [--type=classic] \
    [--auto-delete=false] \
    [--vhost=default] \
    [--transport=default]
```

**Options:**
- `queue-name`: Name of the queue to create (required)
- `--durable`: Whether the queue survives broker restarts (default: `true`)
- `--type`: Queue type - `classic`, `quorum`, or `stream` (default: `classic`)
- `--auto-delete`: Automatically delete when no longer in use (default: `false`)
- `--vhost`: Virtual host where the queue will be created (default: `default`)
- `--transport`: Transport configuration to use (default: `default`)

**Queue Types Explained:**
- **classic**: Traditional RabbitMQ queue, best for most use cases
- **quorum**: High availability queue with better data safety guarantees
- **stream**: Append-only log with non-destructive consumer semantics

**Examples:**

```bash
# Create a basic durable queue
php rabbitmq-utils queue:create my-queue

# Create a quorum queue (for high availability)
php rabbitmq-utils queue:create my-quorum-queue --type=quorum

# Create a temporary auto-delete queue
php rabbitmq-utils queue:create temp-queue --durable=false --auto-delete=true

# Create a queue in a specific vhost
php rabbitmq-utils queue:create orders-queue --vhost=production

# Create a stream queue for event logs
php rabbitmq-utils queue:create event-stream --type=stream --transport=production
```

#### List Messages in a Queue

Peek at messages in a queue without removing them. Useful for debugging and monitoring.

```bash
php rabbitmq-utils message:list <queue-name> \
    [--count=10] \
    [--payload-only] \
    [--format=table] \
    [--vhost=default] \
    [--transport=default]
```

**Options:**
- `queue-name`: Name of the queue to inspect (required)
- `--count`: Number of messages to retrieve (default: `10`)
- `--payload-only`: Show only message payloads, hide headers
- `--format`: Output format - `table` or `raw` (default: `table`)
- `--vhost`: Virtual host containing the queue (default: `default`)
- `--transport`: Transport configuration to use (default: `default`)

**Examples:**

```bash
# View first 10 messages in a queue
php rabbitmq-utils message:list my-queue

# View first 50 messages
php rabbitmq-utils message:list my-queue --count=50

# View only message payloads (cleaner output)
php rabbitmq-utils message:list my-queue --payload-only

# Raw format output (easier to parse)
php rabbitmq-utils message:list my-queue --format=raw

# Inspect messages in a production queue
php rabbitmq-utils message:list orders-queue --vhost=production --count=5
```

### Shovel Commands

Shovels are used to move or copy messages between queues, exchanges, or even different RabbitMQ brokers. They're perfect for:
- Migrating messages between environments
- Moving messages from one broker to another
- Copying messages for testing or debugging
- Draining queues

#### Create a Shovel

Create a shovel to move messages from a source to a destination.

```bash
php rabbitmq-utils shovel:create <resource> \
    [--transport-src=source] \
    [--transport-dst=destination] \
    [--vhost=default] \
    [--transport=default] \
    [--prefix=shovel] \
    [--force]
```

**Options:**
- `resource`: The queue or exchange name to shovel from (required)
- `--transport-src`: Source transport configuration (default: `source`)
- `--transport-dst`: Destination transport configuration (default: `destination`)
- `--vhost`: Virtual host for shovel management (default: `default`)
- `--transport`: Transport for creating the shovel (default: `default`)
- `--prefix`: Prefix for the shovel name, helps identify shovels (default: `shovel`)
- `--force`: Skip confirmation prompt (useful for automation)

**Examples:**

```bash
# Create a shovel to move messages between environments
php rabbitmq-utils shovel:create my-queue \
    --transport-src=production \
    --transport-dst=staging

# Create a shovel with a custom prefix
php rabbitmq-utils shovel:create orders-queue \
    --prefix=migration \
    --transport-src=old-broker \
    --transport-dst=new-broker

# Create a shovel without confirmation (for scripts)
php rabbitmq-utils shovel:create backup-queue \
    --transport-src=production \
    --transport-dst=backup \
    --force

# Create a shovel in a specific vhost
php rabbitmq-utils shovel:create events-queue \
    --vhost=production \
    --transport-src=source \
    --transport-dst=destination
```

**Use Cases:**

1. **Environment Migration**: Move messages from production to staging for testing
2. **Broker Migration**: Transfer messages when upgrading or moving RabbitMQ servers
3. **Message Copying**: Create a copy of messages for debugging without affecting production
4. **Queue Draining**: Move messages from one queue to another before decommissioning

#### List Shovels

Display all configured shovels and their status.

```bash
php rabbitmq-utils shovel:list [--vhost=default] [--transport=default] [--name-only]
```

**Options:**
- `--vhost`: Virtual host to query (default: `default`)
- `--transport`: Transport configuration to use (default: `default`)
- `--name-only`: Display only shovel names

**Examples:**

```bash
# List all shovels with details
php rabbitmq-utils shovel:list

# List shovels in a specific vhost
php rabbitmq-utils shovel:list --vhost=production

# Get only shovel names
php rabbitmq-utils shovel:list --name-only
```

#### Remove a Shovel

Delete an existing shovel.

```bash
php rabbitmq-utils shovel:remove <shovel-name> [--vhost=default] [--transport=default] [--force]
```

**Options:**
- `shovel-name`: Name of the shovel to remove (required)
- `--vhost`: Virtual host containing the shovel (default: `default`)
- `--transport`: Transport configuration to use (default: `default`)
- `--force`: Skip confirmation prompt

**Examples:**

```bash
# Remove a shovel with confirmation
php rabbitmq-utils shovel:remove shovel-my-queue

# Remove a shovel without confirmation
php rabbitmq-utils shovel:remove migration-orders-queue --force

# Remove a shovel from a specific vhost
php rabbitmq-utils shovel:remove backup-shovel --vhost=production
```

### Transport Commands

Transports define the RabbitMQ connection configurations. You can have multiple transports configured in your `transports.yaml` file for different environments or brokers.

#### List Transports

Display all configured transport connections from your `transports.yaml` file.

```bash
php rabbitmq-utils transport:list
```

This command shows all available transport configurations, helping you verify your setup and see which transports are available for use with other commands.

**Example:**

```bash
# List all configured transports
php rabbitmq-utils transport:list
```

The output will show:
- Transport name
- Connection details (host, port, vhost)
- Available clients (amqp, manager)
- Authentication status

This is useful when you:
- Need to verify your transports.yaml configuration
- Want to see which transports are available for use
- Are troubleshooting connection issues
- Need to reference transport names in other commands

### Common Workflows

#### 1. Migrate Messages Between Environments

```bash
# First, check what queues exist in production
php rabbitmq-utils queue:list --transport=production --name-only

# Create a shovel to copy messages to staging
php rabbitmq-utils shovel:create important-queue \
    --transport-src=production \
    --transport-dst=staging

# Monitor the shovel status
php rabbitmq-utils shovel:list --transport=production

# Once migration is complete, remove the shovel
php rabbitmq-utils shovel:remove shovel-important-queue --transport=production --force
```

#### 2. Debug Queue Issues

```bash
# Check queue details
php rabbitmq-utils queue:list --transport=production

# Inspect messages to see what's in the queue
php rabbitmq-utils message:list problem-queue --count=20 --transport=production

# View just the payloads for easier reading
php rabbitmq-utils message:list problem-queue --payload-only --transport=production
```

#### 3. Setup New Environment

```bash
# List transports to verify configuration
php rabbitmq-utils transport:list

# Create necessary queues
php rabbitmq-utils queue:create orders-queue --type=quorum --transport=staging
php rabbitmq-utils queue:create notifications-queue --transport=staging
php rabbitmq-utils queue:create events-queue --type=stream --transport=staging

# Verify queues were created
php rabbitmq-utils queue:list --transport=staging
```

## Tips and Best Practices

### Understanding Vhosts

- The `--vhost` parameter in commands refers to the virtual host name configured in your RabbitMQ server
- The default value `default` refers to the transport configuration name, not the actual vhost
- When you specify `--vhost=production`, you're telling the tool to work with the vhost configured in your selected transport

### Working with Multiple Environments

1. **Use descriptive transport names**: Name your transports after environments (e.g., `production`, `staging`, `development`)
2. **Separate credentials**: Use different users with appropriate permissions for each environment
3. **Enable TLS in production**: Always use `tls: true` and `tls_verify: true` for production environments

### Scripting and Automation

The `--name-only` flag is perfect for piping output to other commands:

```bash
# Get all queue names and process them
php rabbitmq-utils queue:list --name-only | while read queue; do
    echo "Processing $queue"
    php rabbitmq-utils message:list "$queue" --count=1
done
```

Use `--force` to skip confirmations in automated scripts:

```bash
# Automated shovel creation
php rabbitmq-utils shovel:create backup-queue \
    --transport-src=production \
    --transport-dst=backup \
    --force
```

### Performance Considerations

- **Queue Types**: 
  - Use `classic` queues for general purpose, high-throughput scenarios
  - Use `quorum` queues when data safety and high availability are critical
  - Use `stream` queues for event logs and time-series data
  
- **Message Inspection**: When inspecting large queues, use `--count` to limit results and avoid overwhelming output

### Security Best Practices

1. **Never commit `transports.yaml` with real credentials** - Add it to `.gitignore`
2. **Use environment-specific credentials** - Don't use `guest/guest` in production
3. **Enable TLS verification** - Set `tls_verify: true` in production
4. **Limit Management API access** - Use firewall rules to restrict port 15672

### Troubleshooting

**Connection Issues:**
```bash
# Verify your transport configuration
php rabbitmq-utils transport:list

# Test with a simple command
php rabbitmq-utils queue:list --transport=default
```

**Permission Errors:**
- Ensure your user has the correct permissions on the vhost
- Check that management plugin is enabled on RabbitMQ server
- Verify the management API port (15672) is accessible

**TLS/SSL Errors:**
- Try setting `tls_verify: false` for testing (not recommended for production)
- Ensure your certificates are valid and properly configured
- Check that the hostname matches the certificate CN/SAN

## Development

### Running Tests

```bash
composer test
```

Or use Pest directly:
```bash
./vendor/bin/pest
```

### Code Style

Format code with Laravel Pint:
```bash
./vendor/bin/pint
```

### Building

Build a standalone executable:
```bash
php rabbitmq-utils app:build
```

## Docker Support

A Docker Compose configuration is included for testing:

```bash
docker-compose up -d
```

This will start a RabbitMQ instance accessible at:
- AMQP: `localhost:5672`
- Management UI: `http://localhost:15672`

Default credentials: `guest`/`guest`

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Author

Pavlo Shamrai

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

