<?php

namespace Zls\Command;

use Z;

/**
 * Command.
 *
 * @author        影浅-Seekwe
 * @email         seekwe@gmail.com
 * @updatetime    2018-7-22 13:37:09
 */
abstract class Command
{
    use Utils;

    public function help($args, $command = null)
    {
        $command = $command
            ?: Z::arrayGet(explode(':', Z::arrayGet($args, 1)), 0);
        $options = static::options();
        $handles = static::handle();
        if (true === $handles) {
            $handles = $this->getHandle();
        }
        $commandPrefix = z::arrayGet($args, 0);
        $commandStr = $commandPrefix.' '.$this->color($command);
        $usage = '';
        $usage .= $handles ? $this->color(':{handle}', 'cyan') : '';
        $usage .= $options ? $this->color(' [options ...]', 'dark_gray')
            : '';
        $example = static::example();
        $this->printStrN(static::description(), 'light_green');
        //$this->printStrN();
        //$this->printStrN('Usage:', 'yellow');
        //$this->printStrN('  '.$commandStr.$usage);
        if ($handles) {
            $this->printStrN();
            $this->printStrN('Handle:', 'yellow');
            foreach (
                $this->beautify($handles, $command, 'cyan', ':') as $k => $v
            ) {
                $this->printStrN('  '.$commandPrefix.' '.ltrim($v));
            }
        }
        if ($options) {
            $this->printStrN();
            $this->printStrN('Options:', 'yellow');
            foreach ($this->beautify($options, '', 'dark_gray') as $k => $v) {
                $this->printStrN($v);
            }
        }
        if ($example) {
            $this->printStrN();
            $this->printStrN('Example:', 'yellow', '');
            foreach (
                $this->beautify($example, z::arrayGet($args, 0).' '.$command,
                    'cyan', false) as $k => $v
            ) {
                $this->printStrN($v);
            }
        }
    }

    /**
     * 命令配置.
     *
     * @return array
     */
    abstract public function options();

    /**
     * 子命令.
     *
     * @return array
     */
    public function handle()
    {
    }

    final public function getHandle()
    {
        $keys = array_diff(get_class_methods($this),
            get_class_methods(__CLASS__));

        return array_fill_keys($keys, '');
    }

    /**
     * 命令示例.
     *
     * @return array
     */
    public function example()
    {
        return [];
    }

    /**
     * 命令介绍.
     *
     * @return string
     */
    abstract public function description();

    final public function beautify(
        $commands,
        $command = '',
        $color = '',
        $pre = ' '
    ) {
        $lists = [];
        $isAssoc = function ($array) {
            if (is_array($array)) {
                $keys = array_keys($array);

                return join('|', $keys) != join('|', array_keys($keys));
            }

            return false;
        };
        $is = $isAssoc($commands);
        $maxLen = 10;
        $_tmp = array_keys($commands);
        usort($_tmp, function ($e, $c) {
            return strlen($e) < strlen($c);
        });
        $len = strlen($_tmp[0]);
        if ($maxLen > $len) {
            $len = $maxLen;
        }
        foreach ($commands as $key => $value) {
            $m = $this->color(str_pad($key, $len), $color);
            if (!z::strBeginsWith($key, ' ') && !z::strBeginsWith($key, ':')) {
                if (false === $pre) {
                    $command = '';
                } else {
                    $m = $pre.$m;
                }
            }
            $lists[] = !$is ? '  '.$command.$value
                : '  '.$command.$m.'  '.$value;
        }

        return $lists;
    }

    /**
     * 命令默认执行.
     *
     * @param $args
     *
     * @return mixed
     */
    abstract public function execute($args);
}
