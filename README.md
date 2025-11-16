# PHP Startup Parameter Optimization

[English](README.md) | [中文](README.zh-CN.md)

A PHP library that provides optimized startup parameters for OPcache and JIT configuration to improve performance.

## Features

- 🚀 **Automatic Detection**: Detects OPcache and JIT support automatically
- ⚡ **Performance Optimization**: Provides optimized parameters for better performance
- 🔧 **Flexible Configuration**: Customizable OPcache and JIT settings
- 📊 **Status Reporting**: Get detailed information about optimization support
- =� **Safe Fallbacks**: Gracefully handles unsupported environments

## Requirements

- PHP 8.0 or higher
- OPcache extension (recommended for JIT)

## Installation

```bash
composer require tourze/php-startup-parameter-optimization
```

## Usage

### Basic Usage

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();

// Check if OPcache and JIT are supported
if ($optimizer->isOpcacheSupported()) {
    echo "OPcache is supported\n";
}

if ($optimizer->isJitSupported()) {
    echo "JIT is supported\n";
}

// Get optimized parameters
$parameters = $optimizer->getOptimizedParameters();
print_r($parameters);
```

### Command Line Usage

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();
$parameters = $optimizer->getOptimizedParameters();

// Execute PHP with optimized parameters
$command = 'php ' . implode(' ', $parameters) . ' your_script.php';
passthru($command);
```

### Custom Configuration

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();

// Get only OPcache parameters
$opcacheParams = $optimizer->getOpcacheParameters();

// Get only JIT parameters with custom buffer size
$jitParams = $optimizer->getJitParameters('200M');

// Get parameters with custom settings
$parameters = $optimizer->getOptimizedParameters(
    enableOpcache: true,
    enableJit: true,
    jitBufferSize: '150M'
);
```

### Status Information

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();
$status = $optimizer->getStatus();

echo "PHP Version: " . $status['php_version'] . "\n";
echo "OPcache Support: " . ($status['opcache'] ? 'Yes' : 'No') . "\n";
echo "JIT Support: " . ($status['jit'] ? 'Yes' : 'No') . "\n";

if (!empty($status['reasons'])) {
    echo "Unsupported Reasons:\n";
    foreach ($status['reasons'] as $feature => $reason) {
        echo "- $feature: $reason\n";
    }
}
```

## Default Parameters

### OPcache Parameters

When OPcache is supported, the following parameters are applied:

- `opcache.enable_cli=1` - Enable OPcache in CLI mode
- `opcache.max_accelerated_files=50000` - Maximum number of accelerated files
- `opcache.memory_consumption=256` - OPcache memory consumption in MB
- `opcache.interned_strings_buffer=16` - Interned strings buffer in MB
- `opcache.fast_shutdown=1` - Enable fast shutdown
- `opcache.validate_timestamps=0` - Disable timestamp validation for production

### JIT Parameters

When JIT is supported (PHP 8.0+), the following parameters are applied:

- `opcache.jit=tracing` - Use tracing JIT mode
- `opcache.jit_buffer_size=100M` - JIT buffer size (default)
- `opcache.jit_hot_loop=64` - Hot loop threshold
- `opcache.jit_hot_func=127` - Hot function threshold
- `opcache.jit_hot_return=127` - Hot return threshold
- `opcache.jit_hot_side_exit=127` - Hot side exit threshold

## Configuration Constants

You can customize default values by extending the `PhpOptimizer` class:

```php
class CustomPhpOptimizer extends PhpOptimizer
{
    protected const DEFAULT_JIT_BUFFER_SIZE = '200M';
    protected const DEFAULT_OPCACHE_MEMORY = 512;
    protected const DEFAULT_OPCACHE_MAX_FILES = 100000;
}
```

## Performance Tips

1. **Production Environment**: Always disable `opcache.validate_timestamps` in production
2. **Memory Allocation**: Adjust `opcache.memory_consumption` based on your application size
3. **JIT Buffer**: Increase `opcache.jit_buffer_size` for larger applications
4. **File Count**: Set `opcache.max_accelerated_files` higher than your total file count

## Troubleshooting

### Common Issues

1. **JIT not available**
    - Ensure PHP 8.0 or higher
    - Check if OPcache extension is loaded
    - Verify PHP was compiled with JIT support

2. **OPcache not working**
    - Install and enable OPcache extension
    - Check PHP configuration: `php -m | grep OPcache`

3. **Performance degradation**
    - Monitor OPcache statistics with `opcache_get_status()`
    - Adjust memory settings if cache is full

### Debug Information

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();
$status = $optimizer->getStatus();

// Debug information
var_dump($status);

// Check current OPcache status
if (function_exists('opcache_get_status')) {
    var_dump(opcache_get_status());
}
```

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This library is released under the MIT License. See the [LICENSE](LICENSE) file for details.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.