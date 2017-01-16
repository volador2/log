<?php

namespace Volador\Log;

use Volador\Log\LogLevel;
use Volador\Log\LogConfig;
use Volador\Helpers\Debug;
use Volador\Helpers\CFile;
use Volador\Helpers\ArrayHelp;

/**
* 日志处理类
* 注1:日志切割配合shell脚本处理, 不在PHP中浪费CPU处理这种事情。
*
* 配置日志格式化模版:
*  LogConfig::template("{DATETIME} [{TRACE_ID}][{LEVEL}] {FILE}:{LINE} {CONTENT}");
*
* 給自定义模版变量复值:
*  Logger::templateSet("TRACE_ID", $_REQUEST['request_id']);
*
* 写入日志:
*  Logger::debug('hello');
*  Logger::debug('hello, {name} !', array('name' => 'Word'));
*
* 守护进程模式
* LogConfig::logfile("/Users/chao/logs/debug.log");
* LogConfig::template("{DATETIME} [{TRACE_ID}][{LEVEL}] {FILE}:{LINE} {CONTENT}");
* function daemon() {
*     LogConfig::setTemplateVal('TRACE_ID', $TRACE_ID);
*     Logger::debug('hello, {name}', ['name' => 'Lin{name}']);
*
*     // todo:
*     Logger::fflush();
* }
* 
* @since [version> [<description>]
* @author huangchao <[<email address>]>
*/
class Logger
{
    /**
     * [$_fd description]
     * @var [type]
     */
    static protected $_fd;

    /**
     * 致命的运行时错误。这类错误一般是不可恢复的情况。后果是导致脚本终止不再继续运行。
     * @param  [type] $message [description]
     * @param  array  $context [description]
     * @return [type]          [description]
     */
    static public function fatal($message, array $context = array())
    {
        self::log(LogLevel::FATAL, $message, $context);
    }

    /**
     * 运行时警告 (非致命错误)。仅给出提示信息，但是脚本不会终止运行。
     * @param  [type] $message [description]
     * @param  array  $context [description]
     * @return [type]          [description]
     */
    static public function warning($message, array $context = array())
    {
        self::log(LogLevel::WARNING, $message, $context);
    }

    /**
     * 表示遇到可能会表现为错误的情况，但是在可以正常运行的脚本里面也可能会有类似的通知。
     * @param  [type] $message [description]
     * @param  array  $context [description]
     * @return [type]          [description]
     */
    static public function notice($message, array $context = array())
    {
        self::log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * [debug description]
     * @param  [type] $message [description]
     * @param  array  $context [description]
     * @return [type]          [description]
     */
    static public function debug($message, array $context = array())
    {
        self::log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * [log description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    static protected function log($level, $message, array $context = array(), $deep = 1)
    {
        $v = array();
        $template_val = LogConfig::getTemplateVal();
        if (!empty($template_val) && is_array($template_val)) {
            foreach ($template_val as $key => $val) {
                $v[$key] = $val;
            }
        }

        // 匹配模版解析规则获取对应数据
        $bt         = Debug::backtrace($deep + 1);
        $template   = LogConfig::template();

        if (strpos($template, '{DATETIME}') !== FALSE) {
            $v['DATETIME'] = date('Y/m/d H:i:s');
        }

        if (strpos($template, '{LEVEL}') !== FALSE) {
            $v['LEVEL'] = $level;
        }

        if (strpos($template, '{FILE}') !== FALSE) {
            $v['FILE'] = ArrayHelp::value($bt, 'file', '-');
        }

        if (strpos($template, '{LINE}') !== FALSE) {
            $v['LINE'] = ArrayHelp::value($bt, 'line', '-');
        }

        $v['CONTENT'] = self::interpolate($message, $context);

        // write
        self::write($template, $v);
    }

    /**
     * 写入持续化存储, 避免其他模块和Logger相互依赖, 这里自己处理写入文件的工作, 
     * 如果文件系统出现故障, 则丢弃该日志.
     * @param  [type] $message [description]
     * @param  [type] $context [description]
     * @return [type]          [description]
     */
    static protected function write($template, array $context = array())
    {
        $str = self::interpolate($template, $context);

        $f = self::fileInstance();
        $f->write($str . PHP_EOL);

        // 立即写盘
        if (isset($context['LEVEL'])) {
            if (LogLevel::FATAL === $context['LEVEL'] || LogLevel::WARNING === $context['LEVEL']) {
                $f->fflush();
            }
        }
    }

    /**
     * [file 实例]
     * @return [type] [description]
     */
    static public function fileInstance()
    {
        if (!self::$_fd) {
            self::$_fd = new CFile(LogConfig::logfile(), "ab+");
        }

        return self::$_fd;
    }

    /**
     * [写盘并释放fd]
     * @return [type] [description]
     */
    static public function fflush()
    {
        $f = self::fileInstance();
        $f->fflush();

        self::$_fd = null;
    }

    /**
     * [interpolate description]
     * @param  [type] $message [description]
     * @param  array  $context [description]
     * @return [type]          [description]
     */
    static protected function interpolate($message, array $context = array())
    {
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        return strtr($message, $replace);
    }
}
