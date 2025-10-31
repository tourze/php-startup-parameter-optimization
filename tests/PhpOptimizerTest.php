<?php

declare(strict_types=1);

namespace Tourze\PHPStartupParameterOptimization\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

/**
 * @internal
 */
#[CoversClass(PhpOptimizer::class)]
final class PhpOptimizerTest extends TestCase
{
    private PhpOptimizer $optimizer;

    public function testOpcacheDetection(): void
    {
        $isSupported = $this->optimizer->isOpcacheSupported();

        // Should match actual extension status
        $this->assertSame(extension_loaded('Zend OPcache'), $isSupported);
    }

    public function testJitDetection(): void
    {
        $isSupported = $this->optimizer->isJitSupported();

        // JIT requires PHP 8.0+ and OPcache
        if (!extension_loaded('Zend OPcache')) {
            $this->assertFalse($isSupported);
        } else {
            // For PHP 8.0+ with OPcache, check actual JIT availability
            $this->assertIsBool($isSupported);
        }
    }

    public function testGetOpcacheParametersWhenSupported(): void
    {
        if (!$this->optimizer->isOpcacheSupported()) {
            $this->assertEmpty($this->optimizer->getOpcacheParameters());

            return;
        }

        $parameters = $this->optimizer->getOpcacheParameters();

        $this->assertContainsEquals('-d', $parameters);
        $this->assertContainsEquals('opcache.enable_cli=1', $parameters);
        $this->assertContainsEquals('opcache.max_accelerated_files=50000', $parameters);
        $this->assertContainsEquals('opcache.memory_consumption=256', $parameters);
        $this->assertContainsEquals('opcache.interned_strings_buffer=16', $parameters);
        $this->assertContainsEquals('opcache.fast_shutdown=1', $parameters);
        $this->assertContainsEquals('opcache.validate_timestamps=0', $parameters);

        // Check that parameters come in pairs (-d followed by value)
        $count = count($parameters);
        $this->assertSame(0, $count % 2);

        for ($i = 0; $i < $count; $i += 2) {
            $this->assertSame('-d', $parameters[$i]);
            $this->assertIsString($parameters[$i + 1]);
        }
    }

    public function testGetJitParametersWhenSupported(): void
    {
        $this->assertInstanceOf(PhpOptimizer::class, $this->optimizer);

        if (!$this->optimizer->isJitSupported()) {
            $this->assertEmpty($this->optimizer->getJitParameters());

            return;
        }

        $parameters = $this->optimizer->getJitParameters();

        $this->assertContainsEquals('-d', $parameters);
        $this->assertContainsEquals('opcache.jit=tracing', $parameters);
        $this->assertContainsEquals('opcache.jit_buffer_size=100M', $parameters);
        $this->assertContainsEquals('opcache.jit_hot_loop=64', $parameters);
        $this->assertContainsEquals('opcache.jit_hot_func=127', $parameters);
        $this->assertContainsEquals('opcache.jit_hot_return=127', $parameters);
        $this->assertContainsEquals('opcache.jit_hot_side_exit=127', $parameters);
    }

    public function testGetJitParametersWithCustomBufferSize(): void
    {
        $this->assertInstanceOf(PhpOptimizer::class, $this->optimizer);

        if (!$this->optimizer->isJitSupported()) {
            $this->assertEmpty($this->optimizer->getJitParameters('200M'));

            return;
        }

        $parameters = $this->optimizer->getJitParameters('200M');

        $this->assertContainsEquals('opcache.jit_buffer_size=200M', $parameters);
    }

    public function testGetOptimizedParametersFullOptimization(): void
    {
        $parameters = $this->optimizer->getOptimizedParameters(true, true);

        if ($this->optimizer->isOpcacheSupported()) {
            $this->assertContainsEquals('opcache.enable_cli=1', $parameters);

            if ($this->optimizer->isJitSupported()) {
                $this->assertContainsEquals('opcache.jit=tracing', $parameters);
            }
        } else {
            $this->assertEmpty($parameters);
        }
    }

    public function testGetOptimizedParametersOpcacheOnly(): void
    {
        $parameters = $this->optimizer->getOptimizedParameters(true, false);

        if ($this->optimizer->isOpcacheSupported()) {
            $this->assertContainsEquals('opcache.enable_cli=1', $parameters);
            $this->assertNotContainsEquals('opcache.jit=tracing', $parameters);
        } else {
            $this->assertEmpty($parameters);
        }
    }

    public function testGetOptimizedParametersDisabled(): void
    {
        $parameters = $this->optimizer->getOptimizedParameters(false, false);
        $this->assertEmpty($parameters);
    }

    public function testGetOptimizedParametersJitRequiresOpcache(): void
    {
        // When OPcache is disabled, JIT should not be enabled even if requested
        $parameters = $this->optimizer->getOptimizedParameters(false, true);
        $this->assertEmpty($parameters);
    }

    public function testGetStatus(): void
    {
        $status = $this->optimizer->getStatus();

        $this->assertArrayHasKey('opcache', $status);
        $this->assertArrayHasKey('jit', $status);
        $this->assertArrayHasKey('php_version', $status);
        $this->assertArrayHasKey('reasons', $status);

        $this->assertIsBool($status['opcache']);
        $this->assertIsBool($status['jit']);
        $this->assertSame(PHP_VERSION, $status['php_version']);
        $this->assertIsArray($status['reasons']);

        // Check reasons are provided for unsupported features
        if (!$status['opcache']) {
            $this->assertArrayHasKey('opcache', $status['reasons']);
            $this->assertIsString($status['reasons']['opcache']);
        }

        if (!$status['jit']) {
            $this->assertArrayHasKey('jit', $status['reasons']);
            $this->assertIsString($status['reasons']['jit']);
        }
    }

    public function testConstantsAreDefined(): void
    {
        $this->assertSame('100M', PhpOptimizer::DEFAULT_JIT_BUFFER_SIZE);
        $this->assertSame(256, PhpOptimizer::DEFAULT_OPCACHE_MEMORY);
        $this->assertSame(50000, PhpOptimizer::DEFAULT_OPCACHE_MAX_FILES);
        $this->assertSame(16, PhpOptimizer::DEFAULT_OPCACHE_INTERNED_STRINGS_BUFFER);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->optimizer = new PhpOptimizer();
    }
}
