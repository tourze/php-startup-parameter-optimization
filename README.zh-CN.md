# PHP 启动参数优化

[English](README.md) | [中文](README.zh-CN.md)

一个用于优化 OPcache 和 JIT 配置的 PHP 库，提供最佳性能的启动参数。

## 特性

- 🚀 **自动检测**：自动检测 OPcache 和 JIT 支持情况
- ⚡ **性能优化**：提供优化的参数以提升性能
- 🔧 **灵活配置**：可自定义 OPcache 和 JIT 设置
- 📊 **状态报告**：获取优化支持的详细信息
- 🛡️ **安全降级**：优雅处理不支持的环境

## 系统要求

- PHP 8.0 或更高版本
- OPcache 扩展（JIT 功能推荐）

## 安装

```bash
composer require tourze/php-startup-parameter-optimization
```

## 使用方法

### 基础用法

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();

// 检查 OPcache 和 JIT 是否支持
if ($optimizer->isOpcacheSupported()) {
    echo "支持 OPcache\n";
}

if ($optimizer->isJitSupported()) {
    echo "支持 JIT\n";
}

// 获取优化参数
$parameters = $optimizer->getOptimizedParameters();
print_r($parameters);
```

### 命令行使用

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();
$parameters = $optimizer->getOptimizedParameters();

// 使用优化参数执行 PHP 脚本
$command = 'php ' . implode(' ', $parameters) . ' your_script.php';
passthru($command);
```

### 自定义配置

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();

// 仅获取 OPcache 参数
$opcacheParams = $optimizer->getOpcacheParameters();

// 获取自定义缓冲区大小的 JIT 参数
$jitParams = $optimizer->getJitParameters('200M');

// 使用自定义设置获取参数
$parameters = $optimizer->getOptimizedParameters(
    enableOpcache: true,
    enableJit: true,
    jitBufferSize: '150M'
);
```

### 状态信息

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();
$status = $optimizer->getStatus();

echo "PHP 版本: " . $status['php_version'] . "\n";
echo "OPcache 支持: " . ($status['opcache'] ? '是' : '否') . "\n";
echo "JIT 支持: " . ($status['jit'] ? '是' : '否') . "\n";

if (!empty($status['reasons'])) {
    echo "不支持的原因:\n";
    foreach ($status['reasons'] as $feature => $reason) {
        echo "- $feature: $reason\n";
    }
}
```

## 默认参数

### OPcache 参数

当 OPcache 支持时，应用以下参数：

- `opcache.enable_cli=1` - 在 CLI 模式下启用 OPcache
- `opcache.max_accelerated_files=50000` - 最大加速文件数量
- `opcache.memory_consumption=256` - OPcache 内存消耗（MB）
- `opcache.interned_strings_buffer=16` - 字符串缓冲区（MB）
- `opcache.fast_shutdown=1` - 启用快速关闭
- `opcache.validate_timestamps=0` - 生产环境禁用时间戳验证

### JIT 参数

当 JIT 支持时（PHP 8.0+），应用以下参数：

- `opcache.jit=tracing` - 使用追踪 JIT 模式
- `opcache.jit_buffer_size=100M` - JIT 缓冲区大小（默认）
- `opcache.jit_hot_loop=64` - 热循环阈值
- `opcache.jit_hot_func=127` - 热函数阈值
- `opcache.jit_hot_return=127` - 热返回阈值
- `opcache.jit_hot_side_exit=127` - 热侧边退出阈值

## 配置常量

您可以通过继承 `PhpOptimizer` 类来自定义默认值：

```php
class CustomPhpOptimizer extends PhpOptimizer
{
    protected const DEFAULT_JIT_BUFFER_SIZE = '200M';
    protected const DEFAULT_OPCACHE_MEMORY = 512;
    protected const DEFAULT_OPCACHE_MAX_FILES = 100000;
}
```

## 性能优化建议

1. **生产环境**：在生产环境中始终禁用 `opcache.validate_timestamps`
2. **内存分配**：根据应用程序大小调整 `opcache.memory_consumption`
3. **JIT 缓冲区**：为大型应用程序增加 `opcache.jit_buffer_size`
4. **文件数量**：将 `opcache.max_accelerated_files` 设置为高于总文件数

## 故障排除

### 常见问题

1. **JIT 不可用**
    - 确保 PHP 8.0 或更高版本
    - 检查是否加载了 OPcache 扩展
    - 验证 PHP 编译时是否包含 JIT 支持

2. **OPcache 不工作**
    - 安装并启用 OPcache 扩展
    - 检查 PHP 配置：`php -m | grep OPcache`

3. **性能下降**
    - 使用 `opcache_get_status()` 监控 OPcache 状态
    - 如果缓存满了，调整内存设置

### 调试信息

```php
<?php

use Tourze\PHPStartupParameterOptimization\PhpOptimizer;

$optimizer = new PhpOptimizer();
$status = $optimizer->getStatus();

// 调试信息
var_dump($status);

// 检查当前 OPcache 状态
if (function_exists('opcache_get_status')) {
    var_dump(opcache_get_status());
}
```

## 测试

```bash
composer test
```

## 贡献

欢迎贡献代码！请随时提交 Pull Request。

## 许可证

本库基于 MIT 许可证发布。详情请参阅 [LICENSE](LICENSE) 文件。

## 更新日志

请查看 [CHANGELOG.md](CHANGELOG.md) 了解最近的更新信息。