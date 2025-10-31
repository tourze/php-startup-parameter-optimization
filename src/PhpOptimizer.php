<?php

declare(strict_types=1);

namespace Tourze\PHPStartupParameterOptimization;

final class PhpOptimizer
{
    public const DEFAULT_JIT_BUFFER_SIZE = '100M';
    public const DEFAULT_OPCACHE_MEMORY = 256;
    public const DEFAULT_OPCACHE_MAX_FILES = 50000;
    public const DEFAULT_OPCACHE_INTERNED_STRINGS_BUFFER = 16;

    private bool $opcacheSupported;

    private bool $jitSupported;

    public function __construct()
    {
        $this->opcacheSupported = $this->detectOpcacheSupport();
        $this->jitSupported = $this->detectJitSupport();
    }

    private function detectOpcacheSupport(): bool
    {
        return extension_loaded('Zend OPcache');
    }

    private function detectJitSupport(): bool
    {
        // JIT requires PHP 8.0+
        // @phpstan-ignore-next-line
        if (PHP_VERSION_ID < 80000) {
            return false;
        }

        // JIT requires OPcache
        if (!$this->detectOpcacheSupport()) {
            return false;
        }

        // Check if JIT is available
        $jitEnabled = ini_get('opcache.jit');
        if (false === $jitEnabled) {
            return false;
        }

        // Check compile-time JIT support
        if (defined('ZEND_JIT_AVAILABLE')) {
            return (bool) constant('ZEND_JIT_AVAILABLE');
        }

        // PHP 8.0+ should support JIT by default
        return true;
    }

    public function isOpcacheSupported(): bool
    {
        return $this->opcacheSupported;
    }

    public function isJitSupported(): bool
    {
        return $this->jitSupported;
    }

    /**
     * @return array<int, string>
     */
    public function getOptimizedParameters(
        bool $enableOpcache = true,
        bool $enableJit = true,
        string $jitBufferSize = self::DEFAULT_JIT_BUFFER_SIZE,
    ): array {
        $parameters = [];

        if ($enableOpcache && $this->opcacheSupported) {
            $parameters = array_merge($parameters, $this->getOpcacheParameters());

            if ($enableJit && $this->jitSupported) {
                $parameters = array_merge($parameters, $this->getJitParameters($jitBufferSize));
            }
        }

        return $parameters;
    }

    /**
     * @return array<int, string>
     */
    public function getOpcacheParameters(): array
    {
        if (!$this->opcacheSupported) {
            return [];
        }

        return [
            '-d',
            'opcache.enable_cli=1',
            '-d',
            'opcache.max_accelerated_files=' . self::DEFAULT_OPCACHE_MAX_FILES,
            '-d',
            'opcache.memory_consumption=' . self::DEFAULT_OPCACHE_MEMORY,
            '-d',
            'opcache.interned_strings_buffer=' . self::DEFAULT_OPCACHE_INTERNED_STRINGS_BUFFER,
            '-d',
            'opcache.fast_shutdown=1',
            '-d',
            'opcache.validate_timestamps=0',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function getJitParameters(string $bufferSize = self::DEFAULT_JIT_BUFFER_SIZE): array
    {
        if (!$this->jitSupported || !$this->opcacheSupported) {
            return [];
        }

        return [
            '-d',
            'opcache.jit=tracing',
            '-d',
            'opcache.jit_buffer_size=' . $bufferSize,
            '-d',
            'opcache.jit_hot_loop=64',
            '-d',
            'opcache.jit_hot_func=127',
            '-d',
            'opcache.jit_hot_return=127',
            '-d',
            'opcache.jit_hot_side_exit=127',
        ];
    }

    /**
     * @return array{opcache: bool, jit: bool, php_version: string, reasons: array<string, string>}
     */
    public function getStatus(): array
    {
        $reasons = [];

        if (!$this->opcacheSupported) {
            $reasons['opcache'] = $this->getOpcacheUnsupportedReason();
        }

        if (!$this->jitSupported) {
            $reasons['jit'] = $this->getJitUnsupportedReason();
        }

        return [
            'opcache' => $this->opcacheSupported,
            'jit' => $this->jitSupported,
            'php_version' => PHP_VERSION,
            'reasons' => $reasons,
        ];
    }

    private function getOpcacheUnsupportedReason(): string
    {
        if (!extension_loaded('Zend OPcache')) {
            return 'OPcache extension not loaded';
        }

        return 'Unknown reason';
    }

    private function getJitUnsupportedReason(): string
    {
        // @phpstan-ignore-next-line
        if (PHP_VERSION_ID < 80000) {
            return sprintf('PHP version %s is below 8.0', PHP_VERSION);
        }

        if (!$this->detectOpcacheSupport()) {
            return 'JIT requires OPcache to be enabled';
        }

        $jitEnabled = ini_get('opcache.jit');
        if (false === $jitEnabled) {
            return 'opcache.jit configuration not available';
        }

        if (defined('ZEND_JIT_AVAILABLE') && !constant('ZEND_JIT_AVAILABLE')) {
            return 'JIT not available at compile time';
        }

        return 'Unknown reason';
    }
}
