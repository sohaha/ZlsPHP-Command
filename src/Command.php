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

    final public function help($args)
    {
        $command = Z::arrayGet(explode(':', Z::arrayGet($args, 1)), 0);
        $options = static::options($args);
        $handles = static::handle($args);
        if ($handles === true) {
            $handles = $this->getHandle();
        }
        $commandStr = z::arrayGet($args, 0) . ' ' . $this->color($command);
        $usage = '';
        $usage .= $handles ? $this->color(':{handle}', 'cyan') : '';
        $usage .= $options ? $this->color(' [options ...]', 'blue') : '';
        $example = static::example($args);
        $this->printStrN(static::description($args), 'light_green');
        $this->printStrN();
        $this->printStrN('Usage:', 'yellow');
        $this->printStrN('  ' . $commandStr . $usage);
        if ($handles) {
            $this->printStrN();
            $this->printStrN('Handle:', 'yellow');
            foreach ($this->beautify($handles) as $k => $v) {
                $this->printStrN('  ' . z::arrayGet($args, 0) . ' ' . $command . $this->color(':' . $k, 'cyan') . '    ' . $v);
            }
        }
        if ($options) {
            $this->printStrN();
            $this->printStrN('Options:', 'yellow');
            foreach ($this->beautify($options) as $k => $v) {
                $this->printStrN('  ' . $this->color($k, 'blue') . '    ' . $v);
            }
        }
        if ($example) {
            $this->printStrN();
            $this->printStrN('Example:', 'yellow');
            foreach ($this->beautify($example) as $k => $v) {
                $this->printStrN('  ' . z::arrayGet($args, 0) . ' ' . $command . $this->color($k, 'cyan') . '    ' . $v);
            }
        }
    }

    /**
     * 命令配置
     * @return array
     */
    abstract public function options();

    /**
     * 子命令
     * @return array
     */
    public function handle()
    {
    }

    final public function getHandle()
    {
        $keys = array_diff(get_class_methods($this), get_class_methods(__CLASS__));

        return array_fill_keys($keys, '--');
    }

    /**
     * 命令示例
     * @return array
     */
    public function example()
    {
    }

    /**
     * 命令介绍
     * @return string
     */
    abstract public function description();

    final public function beautify($commands)
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
}
