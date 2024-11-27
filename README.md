# LaraSendy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/skaisser/larasendy.svg?style=flat-square)](https://packagist.org/packages/skaisser/larasendy)
[![Tests](https://img.shields.io/github/actions/workflow/status/skaisser/larasendy/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/skaisser/larasendy/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/skaisser/larasendy.svg?style=flat-square)](https://packagist.org/packages/skaisser/larasendy)
[![License](https://img.shields.io/packagist/l/skaisser/larasendy.svg?style=flat-square)](https://packagist.org/packages/skaisser/larasendy)

> The missing bridge between Laravel and Sendy.co for seamless email marketing automation

## Why LaraSendy?

While Sendy.co offers a cost-effective alternative to expensive email marketing services, it lacks official Laravel integration. LaraSendy fills this gap by providing:

- **Automatic Synchronization**: Keep your Laravel users in sync with Sendy lists
- **GDPR Compliance**: Built-in support for GDPR requirements
- **Flexible Mapping**: Map any user field to Sendy's custom fields
- **Zero Configuration**: Works out of the box with sensible defaults
- **Security First**: Built-in spam protection and secure API handling
- **Performance Focused**: Efficient batch processing and minimal overhead

## Requirements

- PHP: ^7.3|^8.0
- Laravel: 6.x|7.x|8.x|9.x|10.x

This package is actively maintained and tested against all supported Laravel versions. We follow Laravel's versioning scheme to ensure compatibility:

| Laravel Version | Package Version | PHP Version |
|----------------|-----------------|-------------|
| 10.x           | 1.x            | ≥ 7.3       |
| 9.x            | 1.x            | ≥ 7.3       |
| 8.x            | 1.x            | ≥ 7.3       |
| 7.x            | 1.x            | ≥ 7.3       |
| 6.x            | 1.x            | ≥ 7.3       |

## Quick Start

### 1️⃣ Installation

```bash
composer require skaisser/larasendy
```

### 2️⃣ Configuration

Publish assets and run migrations:

```bash
php artisan vendor:publish --provider="Skaisser\LaraSendy\SendyServiceProvider"
php artisan migrate
```

### 3️⃣ Environment Setup

Add to your `.env`:

```env
SENDY_URL=https://your-sendy-installation.com
SENDY_API_KEY=your-api-key
SENDY_LIST_ID=your-list-id
SENDY_TARGET_TABLE=users
SENDY_SYNC_INTERVAL=60
SENDY_GDPR=false
SENDY_SILENT=true
SENDY_REFERRER="${APP_URL}"
SENDY_HONEYPOT=false
```

That's it! Your users will now automatically sync with Sendy. 

## Features

### Manual Sync

To manually sync users to Sendy:

```bash
php artisan sendy:sync
```

To force sync all users (including those already synced):

```bash
php artisan sendy:sync --force
```

### Automatic Sync

The package automatically schedules the sync command based on the `SENDY_SYNC_INTERVAL` configuration (default: 60 minutes).

Make sure your Laravel scheduler is running:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Advanced Configuration

### Configuration Options

- `url`: Your Sendy installation URL
- `api_key`: Your Sendy API key
- `list_id`: The ID of the list you want to subscribe users to
- `target_table`: The database table containing users (default: users)
- `sync_interval`: How often should the sync command run in minutes (default: 60)
- `gdpr`: Set to true for GDPR-compliant signup (default: false)
- `silent`: Bypass Double opt-in and use Single Opt-in (default: true)
- `referrer`: URL where users are being signed up from (default: APP_URL)
- `honeypot`: Enable honeypot field to prevent spambots (default: false)
- `fields_mapping`: Map your user table fields to Sendy fields

### Field Mapping

The `fields_mapping` configuration allows you to specify how your database fields map to Sendy fields. The package supports standard Sendy fields and custom fields:

#### Standard Fields

```php
'fields_mapping' => [
    // Required field
    'email' => 'email',     // Maps your database's email field

    // Optional standard fields
    'name' => 'name',       // Maps to Sendy's name field
    'country' => 'country_code',  // Should be 2-letter country code
    'ipaddress' => 'ip_address', // User's IP address
],
```

#### Custom Fields

Any field in the mapping that isn't a standard field (`email`, `name`, `country`, `ipaddress`) will be sent as a custom field. For example:

```php
'fields_mapping' => [
    'email' => 'user_email',         // Required: maps to Sendy's email field
    'name' => 'full_name',           // Optional: maps to Sendy's name field
    'country' => 'country_code',     // Optional: 2-letter country code
    'ipaddress' => 'ip_address',     // Optional: IP address
    'Birthday' => 'birth_date',      // Custom: sent as custom[Birthday]
    'Company' => 'organization',     // Custom: sent as custom[Company]
    'Phone' => 'contact_number',     // Custom: sent as custom[Phone]
],
```

Make sure to create these custom fields in your Sendy installation before using them. In Sendy:
1. Go to your list settings
2. Click on "Custom Fields"
3. Add the custom fields (Birthday, Company, Phone, etc.)
4. The field names in your mapping should match exactly with the ones in Sendy

### GDPR Compliance

If you're collecting data from EU users, you can enable GDPR compliance:

```env
SENDY_GDPR=true
```

This will send the `gdpr=true` parameter with each subscription request.

### Double Opt-in Control

By default, the package uses Single Opt-in (silent mode). To use Double Opt-in:

```env
SENDY_SILENT=false
```

### Spam Protection

Enable the honeypot field to prevent spam subscriptions (Sendy 3.0+ only):

```env
SENDY_HONEYPOT=true
```

## Contributing

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Quick Start for Contributors

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Security

If you discover any security-related issues, please email skaisser@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you find this package helpful, please consider:

- Starring the repository
- Reporting issues
- Contributing to the code
- Spreading the word

Made with by [Samuel Kaisser](https://github.com/skaisser)
