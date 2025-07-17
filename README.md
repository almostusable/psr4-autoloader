# PSR-4 Autoloader

A minimalistic PSR-4 autoloader implementation for PHP projects.

## ⚠️ Warning

**This autoloader is intended for internal usage only.** It is a simple implementation and not recommended for production environments. For production, consider using Composer's built-in autoloader.

## Installation

You can install the package via composer:

```bash
composer require almostusable/psr4-autoloader
```

## Usage

1. Configure your namespace mappings in `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

2. Include the autoloader in your PHP script:

```php
require 'vendor/almostusable/psr4-autoloader/autoloader.php';

// Now you can use classes from your namespaces
use App\Factory\RandomFactory;

$randomFactory = new RandomFactory();
```

## Known Issues and Limitations

1. **Basic Namespace Handling**: The current implementation only checks the first segment of the namespace. This can cause issues with nested namespaces or similar prefixes.

2. **Limited Error Handling**: The composer.json parsing has minimal error handling.

3. **Single Autoloading Standard**: Only supports PSR-4, not other standards like PSR-0 or classmap.

## Potential Improvements

- Implement longest prefix matching for better namespace handling
- Improve error handling for composer.json loading
- Add support for multiple autoloading standards
