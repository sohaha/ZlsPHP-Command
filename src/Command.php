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
        $handles = static::handle($args);//$this->getHandle();
        $commandStr = z::arrayGet($args, 0) . ' ' . $this->color($command);
        $usage = '';
        $usage .= $handles ? $this->color(':{handle}', 'cyan') : '';
        $usage .= $options ? $this->color(' [options ...]', 'blue') : '';
        $example = static::example($args);
        $this->echoN(static::description($args));
        $this->echoN();
        $this->echoN('Usage:', 'yellow');
        $this->echoN('  ' . $commandStr . $usage);
        if ($handles) {
            $this->echoN();
            $this->echoN('Handle:', 'yellow');
            foreach ($this->beautify($handles) as $k => $v) {
                $this->echoN('  ' . z::arrayGet($args, 0) . ' ' . $command . $this->color(':' . $k, 'cyan') . '    ' . $v);
            }
        }
        if ($options) {
            $this->echoN();
            $this->echoN('Options:', 'yellow');
            foreach ($this->beautify($options) as $k => $v) {
                $this->echoN('  ' . $this->color($k, 'blue') . '    ' . $v);
            }
        }
        if ($example) {
            $this->echoN();
            $this->echoN('Example:', 'yellow');
            foreach ($this->beautify($example) as $k => $v) {
                $this->echoN('  ' . z::arrayGet($args, 0) . ' ' . $command . $this->color($k, 'cyan') . '    ' . $v);
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

    final public function getHandle()
    {
        return array_diff(get_class_methods($this), get_class_methods(__CLASS__));
    }
}
