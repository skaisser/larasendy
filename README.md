# LaraSendy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/skaisser/larasendy.svg?style=flat-square)](https://packagist.org/packages/skaisser/larasendy)
[![Total Downloads](https://img.shields.io/packagist/dt/skaisser/larasendy.svg?style=flat-square)](https://packagist.org/packages/skaisser/larasendy)
[![Tests](https://img.shields.io/github/actions/workflow/status/skaisser/larasendy/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/skaisser/larasendy/actions/workflows/run-tests.yml)
[![License](https://img.shields.io/packagist/l/skaisser/larasendy.svg?style=flat-square)](https://packagist.org/packages/skaisser/larasendy)

A Laravel package for seamless integration with Sendy email marketing platform.

## Features

- 🔄 Automatic subscriber synchronization with Sendy
- 🎯 Flexible field mapping
- 🚀 Artisan command for bulk synchronization
- 🔍 Event-driven architecture
- 💾 Cache-based tracking
- ⚡ Efficient chunk processing
- 🛡️ Error handling and logging

## Laravel Support

| Laravel Version | Package Version |
|----------------|-----------------|
| 10.x           | ^1.0           |
| 9.x            | ^1.0           |
| 8.x            | ^1.0           |
| 7.x            | ^1.0           |
| 6.x            | ^1.0           |

## Requirements

- PHP 7.4+
- Laravel 6.0 - 10.0
- Sendy installation

## Installation

```bash
composer require skaisser/larasendy
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Skaisser\LaraSendy\LaraSendyServiceProvider"
```

Configure your `.env` file:

```env
SENDY_URL=your-sendy-installation-url
SENDY_API_KEY=your-api-key
SENDY_LIST_ID=your-list-id
```

## Usage

### Basic Setup

1. Add the `SendySubscriber` trait to your User model:

```php
use Skaisser\LaraSendy\Traits\SendySubscriber;

class User extends Model
{
    use SendySubscriber;
}
```

2. Configure field mapping in `config/sendy.php`:

```php
'fields_mapping' => [
    'email' => 'email',
    'name' => 'name',
    'company' => 'company',
    'country' => 'country'
]
```

### Automatic Synchronization

The package automatically syncs users when they are:
- Created
- Updated
- Deleted (configurable behavior)

### Manual Synchronization

Use the artisan command to sync all subscribers:

```bash
php artisan sendy:sync
```

### Events

The package dispatches events that you can listen to:

- `SendySubscriberSynced`: When a subscriber is successfully synced
- `SendySubscriberFailed`: When sync fails

### Cache Management

Sync status is tracked in cache:
- Success: `sendy_sync_status_{id}`
- Errors: `sendy_sync_error_{id}`

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email the author instead of using the issue tracker.

## Credits

- [Shirleyson Kaisser](https://github.com/skaisser)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
