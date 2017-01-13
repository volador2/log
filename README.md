Volador2 Log
============

##依赖:
 - volador2/helpers


说明
-----

####配置日志模版和处理外部数据

```php
<?php

use Volador\Log\Logger;
use Volador\Log\LogConfig;

class Bootstrap
{
    function ___init() {
        LogConfig::logfile("/var/log/a.log");
        LogConfig::template("{DATETIME} [{REQUEST_ID}][{LEVEL}] {FILE}:{LINE} {CONTENT}");

        // 设置日志模版中KEY的值
        LogConfig::setTemplateVal('REQUEST_ID', $REQUEST_ID);
    }
}
```

####记录日志

```php
use Volador\Log\Logger;
class DemoController
{
    function indexAction()
    {
        Logger::debug('hello, {name}', ['name' => 'Lin{name}']);
    }
}
```

## API 函数列表

当出现致命的运行时错误。这类错误一般是不可恢复的情况。后果是导致脚本终止不再继续运行。

```php
Logger::fatal($message, array $context = array())
```

运行时警告 (非致命错误)。仅给出提示信息，但是脚本不会终止运行。

```php
Logger::warning($message, array $context = array())
```

表示遇到可能会表现为错误的情况，但是在可以正常运行的脚本里面也可能会有类似的通知。

```php
Logger::notice($message, array $context = array())
```

用于帮助调试和查找问题。

```php
Logger::debug($message, array $context = array())
```

