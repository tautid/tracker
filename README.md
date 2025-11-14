# Taut Tracker - Server-Side Pixel Tracking Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tautid/tracker.svg?style=flat-square)](https://packagist.org/packages/tautid/tracker)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/tautid/tracker/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/tautid/tracker/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/tautid/tracker/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/tautid/tracker/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/tautid/tracker.svg?style=flat-square)](https://packagist.org/packages/tautid/tracker)

A comprehensive Laravel package for server-side pixel tracking and conversion management. Track user conversions across multiple advertising platforms (Meta, Google Ads, etc.) with support for multiple drivers, event management, and advanced conversion summaries.

## Features

- 🎯 **Multi-Driver Support** - Track pixels with Meta (Facebook) and easily extend with additional drivers
- 📊 **Conversion Tracking** - Create, manage, and monitor pixel conversions with status tracking
- 🔄 **Event Management** - Define and manage pixel events with flexible driver integration
- 📈 **Conversion Summaries** - Generate daily conversion summaries for analytics
- 🗄️ **MongoDB Integration** - Built-in MongoDB support for high-volume tracking
- ⚡ **Queue Support** - Efficient job queuing for conversion processing
- 🛡️ **Type-Safe** - Full PHP 8.3+ type support with Spatie Laravel Data

## Installation

You can install the package via composer:

```bash
composer require tautid/tracker
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="tracker-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="tracker-config"
```

Optionally publish the views:

```bash
php artisan vendor:publish --tag="tracker-views"
```

## Configuration

After publishing, the config file will be available at `config/taut-tracker.php`:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | List of all drivers supported by this package.
    | Supported: meta
    |
    */
    'drivers' => [
        'meta',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Database setup for MongoDB tracker connection
    |
    */
    'connections' => [
        'taut-mongotrack' => [
            'driver' => 'mongodb',
            'dsn' => env('DB_MONGOTRACK_URI', 'mongodb://localhost:27017'),
            'database' => 'mongotrack',
        ],
    ],
];
```

Ensure your `.env` file includes the MongoDB connection URI:

```env
DB_MONGOTRACK_URI=mongodb://localhost:27017
```

## Usage Guide

### 1. Creating Pixel Events

Pixel events represent conversion actions you want to track. Create them using the `PixelEventService`:

```php
use TautId\Tracker\Services\PixelEventService;
use TautId\Tracker\Data\PixelEvent\CreatePixelEventData;

$service = app(PixelEventService::class);

$pixelEvent = $service->createPixelEvent(
    new CreatePixelEventData(
        name: 'Purchase Conversion',
        driver: 'meta', // or other supported drivers
        event: 'Purchase',
        pixel_id: 'your-meta-pixel-id',
        token: 'your-meta-token',
        reference: null, // Optional model reference
    )
);
```

### 2. Retrieving Pixel Events

Get all pixel events or a specific one:

```php
// Get all pixel events
$allEvents = $service->getAllPixelEvents();

// Get a specific pixel event by ID
$event = $service->getPixelEventById($eventId);

// Get paginated pixel events with filtering
use TautId\Tracker\Data\Utility\FilterPaginationData;

$paginatedEvents = $service->getPaginatedPixelEvents(
    new FilterPaginationData(
        page: 1,
        per_page: 15,
        // Add additional filter criteria
    )
);
```

### 3. Creating Conversions (Pixel Tracking)

Track user conversions with pixel data:

```php
use TautId\Tracker\Services\PixelTrackerService;
use TautId\Tracker\Data\PixelTracker\CreatePixelTrackerData;

$trackerService = app(PixelTrackerService::class);

$conversion = $trackerService->createConversion(
    new CreatePixelTrackerData(
        pixel_id: $pixelEvent->id,
        data: [
            'value' => 99.99,
            'currency' => 'USD',
            'content_name' => 'Product ABC',
        ],
        request: $request, // Current HTTP request for IP, UA, cookies
    )
);
```

The conversion will be created with:
- **Status**: `Queued` (pending processing)
- **Client IP**: Automatically captured from request
- **User Agent**: Automatically captured from request
- **Meta Data**: Facebook pixel cookies (`_fbp`, `_fbc`) if available
- **Source URL**: Page where conversion occurred

### 4. Retrieving Unsaved Conversions

Get conversions that haven't been saved yet (useful for batch processing):

```php
use Carbon\Carbon;

$unsavedConversions = $trackerService->getUnsavedConversionByPixelEvent(
    $eventId,
    Carbon::now()->subDay()
);

// Returns grouped conversions by date for processing
foreach ($unsavedConversions as $date => $conversions) {
    // Process conversions for each date
    foreach ($conversions as $conversion) {
        // Handle conversion
    }
}
```

### 5. Conversion Summaries

Generate daily conversion summaries using the provided command:

```bash
php artisan taut-tracker:create-conversion-summary
```

Or trigger programmatically:

```php
use TautId\Tracker\Services\PixelSummaryService;

$summaryService = app(PixelSummaryService::class);
// Generate summaries (typically called via queue job)
```

## Events & Queue Processing

The package fires a `ConversionCreateEvent` when conversions are created. To process conversions and send them to ad platforms, you need to run Laravel's queue worker:

```php
use TautId\Tracker\Events\ConversionCreateEvent;

Event::listen(ConversionCreateEvent::class, function (ConversionCreateEvent $event) {
    // Handle conversion creation
    $conversion = $event->conversion;
    // Send to ad platform, log, etc.
});
```

**Start the queue worker to process events:**

```bash
# Using queue:work
php artisan queue:work

# Or using Horizon for a dashboard UI
php artisan horizon
```

The status of conversions (`Queued`, `Success`, `Failed`, `Duplicate`) is managed internally by the package through event listeners and queue jobs.

## Data Structures

### PixelEventData

```php
class PixelEventData {
    public string $id;
    public string $name;
    public string $driver;
    public string $event;
    public string $pixel_id;
    public string $token;
    public ?string $reference_type;
    public ?string $reference_id;
    public Carbon $created_at;
    public Carbon $updated_at;
}
```

### PixelTrackerData

```php
class PixelTrackerData {
    public string $id;
    public array $pixel;           // PixelEvent data
    public string $status;         // Queued, Success, Failed, Duplicate
    public bool $is_saved;
    public array $data;            // Conversion data
    public array $meta;            // Driver-specific metadata
    public string $user_agent;
    public string $client_ip;
    public string $source_url;
    public Carbon $created_at;
    public Carbon $updated_at;
}
```

## Supported Drivers

### Meta (Facebook) Driver

The Meta driver automatically captures Facebook pixel cookies and validates conversion data:

```php
'data' => [
    'value' => 99.99,              // Conversion value
    'currency' => 'USD',            // Currency code
    'content_name' => 'Product',    // Item name
    'content_ids' => ['id1', 'id2'], // Optional: item IDs
    'content_type' => 'product',     // Optional: content type
]
```

Captured metadata:
- `fbp`: Facebook browser pixel ID
- `fbc`: Facebook click ID

## Advanced Usage

### Custom Filtering

Use the `FilterServiceTrait` for advanced filtering on pixel events:

```php
use TautId\Tracker\Traits\FilterServiceTrait;

// Available filters: search, sort, date ranges, etc.
$filtered = $service->getPaginatedPixelEvents($filterData);
```

## Database Models

The package provides three main MongoDB models:

- **PixelEvent** - Defines what to track
- **PixelTracker** - Individual conversion records
- **PixelSummary** - Aggregated conversion summaries

All models use MongoDB for scalability and flexibility.

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## Troubleshooting

### MongoDB Connection Issues

Ensure MongoDB is running and the connection string is correct:

```bash
# Test connection
mongosh "mongodb://localhost:27017"
```

### Missing Conversions

Check the conversion status:
- `Queued` - Waiting for processing
- `Success` - Sent to platform
- `Failed` - Failed to send
- `Duplicate` - Duplicate conversion

### Event Listener Not Triggered

Ensure the package is registered in `config/app.php` and service provider is loaded:

```bash
php artisan package:discover
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [tautid](https://github.com/tautid)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
