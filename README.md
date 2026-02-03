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

- **amqp**: For message operations (port 5672)
- **manager**: For management API operations (port 15672)

Example configuration:

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

## Usage

### Queue Commands

#### List Queues

Display all queues in a virtual host with detailed information including message counts, consumers, and queue type.

```bash
php rabbitmq-utils queue:list [--vhost=/] [--transport=default] [--name-only]
```

**Options:**
- `--vhost`: Virtual host to query (default: `/`)
- `--transport`: Transport configuration to use (default: `default`)
- `--name-only`: Display only queue names (useful for scripting)

**Examples:**

```bash
# List all queues in the default vhost with full details
php rabbitmq-utils queue:list

# List queues in a specific vhost
php rabbitmq-utils queue:list --vhost=/production

# Get only queue names (for piping to other commands)
php rabbitmq-utils queue:list --name-only

# Use a different transport configuration
php rabbitmq-utils queue:list --transport=staging
```

#### Create a Queue

Create a new queue with specified properties.

```bash
php rabbitmq-utils queue:create <queue-name> \
    [--durable=true] \
    [--type=classic] \
    [--auto-delete=false] \
    [--vhost=/] \
    [--transport=default]
```

**Options:**
- `queue-name`: Name of the queue to create (required)
- `--durable`: Whether the queue survives broker restarts (default: `true`)
- `--type`: Queue type - `classic`, `quorum`, or `stream` (default: `classic`)
- `--auto-delete`: Automatically delete when no longer in use (default: `false`)
- `--vhost`: Virtual host where the queue will be created (default: `/`)
- `--transport`: Transport configuration to use (default: `default`)

**Examples:**

```bash
# Create a basic durable queue
php rabbitmq-utils queue:create my-queue

# Create a quorum queue (for high availability)
php rabbitmq-utils queue:create my-quorum-queue --type=quorum

# Create a temporary auto-delete queue
php rabbitmq-utils queue:create temp-queue --durable=false --auto-delete=true

# Create a queue in a specific vhost
php rabbitmq-utils queue:create orders-queue --vhost=/production
```

#### List Messages in a Queue

Peek at messages in a queue without removing them. Useful for debugging and monitoring.

```bash
php rabbitmq-utils queue:message:list <queue-name> \
    [--count=10] \
    [--payload-only] \
    [--format=table] \
    [--vhost=/] \
    [--transport=default]
```

**Options:**
- `queue-name`: Name of the queue to inspect (required)
- `--count`: Number of messages to retrieve (default: `10`)
- `--payload-only`: Show only message payloads, hide headers
- `--format`: Output format - `table` or `raw` (default: `table`)
- `--vhost`: Virtual host containing the queue (default: `/`)
- `--transport`: Transport configuration to use (default: `default`)

**Examples:**

```bash
# View first 10 messages in a queue
php rabbitmq-utils queue:message:list my-queue

# View first 50 messages
php rabbitmq-utils queue:message:list my-queue --count=50

# View only message payloads (cleaner output)
php rabbitmq-utils queue:message:list my-queue --payload-only

# Raw format output (easier to parse)
php rabbitmq-utils queue:message:list my-queue --format=raw

# Inspect messages in a production queue
php rabbitmq-utils queue:message:list orders-queue --vhost=/production --count=5
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
    [--vhost=/] \
    [--transport=default] \
    [--prefix=shovel] \
    [--force]
```

**Options:**
- `resource`: The queue or exchange name to shovel from (required)
- `--transport-src`: Source transport configuration (default: `source`)
- `--transport-dst`: Destination transport configuration (default: `destination`)
- `--vhost`: Virtual host for shovel management (default: `/`)
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
    --vhost=/production \
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
php rabbitmq-utils shovel:list [--vhost=/] [--transport=default] [--name-only]
```

**Options:**
- `--vhost`: Virtual host to query (default: `/`)
- `--transport`: Transport configuration to use (default: `default`)
- `--name-only`: Display only shovel names

**Examples:**

```bash
# List all shovels with details
php rabbitmq-utils shovel:list

# List shovels in a specific vhost
php rabbitmq-utils shovel:list --vhost=/production

# Get only shovel names
php rabbitmq-utils shovel:list --name-only
```

#### Remove a Shovel

Delete an existing shovel.

```bash
php rabbitmq-utils shovel:remove <shovel-name> [--vhost=/] [--transport=default] [--force]
```

**Options:**
- `shovel-name`: Name of the shovel to remove (required)
- `--vhost`: Virtual host containing the shovel (default: `/`)
- `--transport`: Transport configuration to use (default: `default`)
- `--force`: Skip confirmation prompt

**Examples:**

```bash
# Remove a shovel with confirmation
php rabbitmq-utils shovel:remove shovel-my-queue

# Remove a shovel without confirmation
php rabbitmq-utils shovel:remove migration-orders-queue --force

# Remove a shovel from a specific vhost
php rabbitmq-utils shovel:remove backup-shovel --vhost=/production
```

### Transport Commands

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

The output will show transport names and their connection details, making it easy to reference them in other commands.

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
php rabbitmq-utils queue:message:list problem-queue --count=20 --transport=production

# View just the payloads for easier reading
php rabbitmq-utils queue:message:list problem-queue --payload-only --transport=production
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

