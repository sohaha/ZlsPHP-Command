<?php

namespace Zls\Command;

use Z;

/**
 * Command
 * @author        影浅-Seekwe
 * @email         seekwe@gmail.com
 * @updatetime    2017-2-27 16:52:51
 */
abstract class Command
{
    use Utils;

    public final function help($args)
    {
        $command = Z::arrayGet(explode(':', Z::arrayGet($args, 1)), 0);
        $options = static::options($args);
        $handles = $this->getHandle();
        $commandStr = z::arrayGet($args, 0) . ' ' . $this->color($command, 'green');
        $usage = '';
        $usage .= $handles ? $this->color('{handle} ', 'cyan') : '';
        $usage .= $options ? $this->color('[options ...] ', 'dark_gray') : '';
        $example = static::example($args);
        $this->strN(static::description($args));
        $this->strN();
        $this->strN('Usage:', 'yellow');
        $this->strN('  ' . $commandStr . ' ' . $this->color($usage, 'dark_gray')
        );
        //if ($handles) {
        //    $this->strN();
        //    $this->strN('Handle:', 'yellow');
        //    foreach ($handles as $k => $handle) {
        //        $this->strN(
        //            '  ' . z::arrayGet($args, 0) . ' ' . $command . ':' . $handle
        //        );
        //    }
        //}
        if ($options) {
            $this->strN();
            $this->strN('Options:', 'yellow');
            foreach ($this->beautify($options) as $k => $v) {
                $this->strN('  ' . $this->color($k, 'green') . '    ' . $v);
            }
        }
        if ($example) {
            $this->strN();
            $this->strN('Example:', 'yellow');
            foreach ($this->beautify($example) as $k => $v) {
                $this->strN('  ' . z::arrayGet($args, 0) . ' ' . $command . $this->color($k, 'green') . '    ' . $v);
            }
        }
    }

    /**
     * 命令配置
     * @return array
     */
    public abstract function options();

    /**
     * 命令示例
     * @return array
     */
    public abstract function example();

    /**
     * 命令介绍
     * @return string
     */
    public abstract function description();

    public final function beautify($commands)
    {
        $lists = [];
        $maxLen = 10;
        foreach ($commands as $key => $value) {
            $len = strlen($key);
            if ($len > $maxLen) {
                $maxLen = $len;
            }
        }
        foreach ($commands as $key => $value) {
            $lists[str_pad($key, $maxLen)] = $value;
        }

        return $lists;
    }

    /**
     * 命令默认执行
     * @param $args
     * @return mixed
     */
    abstract public function execute($args);

    public final function getHandle()
    {
        return array_diff(get_class_methods($this), get_class_methods(__CLASS__));
    }

}
