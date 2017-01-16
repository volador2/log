<?php

namespace Volador\Log;

/**
* LoggerConfig 配置文件类 
* @since [version> [<description>]
* @author huangchao <[<email address>]>
*/
class LogConfig
{
    /**
     * 渲染模版
     * @var string
     */
    static protected $_template = '[{LEVEL}] {FILE}:{LINE} {CONTENT}';

    /**
     * [$_data description]
     * @var [type]
     */
    static protected $_template_data;

    /**
     * 日志保存路径规则
     * @var [type]
     */
    static protected $_filename;

    /**
     * 设置/获取日志解析格式串
     * @param string $tpl [description]
     * @return mixed [<description>]
     */
    static public function template($tpl = null)
    {
        if (!is_null($tpl)) {
            self::$_template = $tpl;
        }

        return self::$_template;
    }

    /**
     * 设置/获取日志保存文件名称
     * @param string $filename [description]
     */
    static public function logfile($filename = null)
    {
        if (!is_null($filename)) {
            self::$_filename = $filename;
        }

        return self::$_filename;
    }

    /**
     * 指定模版变量的值
     * @param [type] $key [description]
     * @param [type] $val [description]
     */
    static public function setTemplateVal($key, $val)
    {
        self::$_template_data[$key] = $val;
    }

    /**
     * 获取模版变量的值
     * @param  string $value [description]
     * @return [mixed]       [description]
     */
    static public function getTemplateVal()
    {
        return self::$_template_data;
    }

}
