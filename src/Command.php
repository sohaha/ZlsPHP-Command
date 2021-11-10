<?php

namespace Zls\Command;

use Z;

/**
 * Command
 */
abstract class Command
{
    use Utils;

    public function help($args, $command = null)
    {
        $command = $command ?: Z::arrayGet(explode(':', Z::arrayGet($args, 1)), 0);
        $options = static::options();
        $handles = static::commands();
        if (true === $handles) {
            $handles = $this->getHandle();
        }
        $commandPrefix = z::arrayGet($args, 0);
        $commandStr    = $commandPrefix . ' ' . $this->color($command);
        $usage         = '';
        $usage         .= $handles ? $this->color(':{command}', 'cyan') : '';
        $usage         .= $options ? $this->color(' [options ...]', 'dark_gray')
            : '';
        $example       = static::example();
        $this->printStrN(static::description(), 'light_green');
        $commandOptions = [];
        if ($handles) {
            $this->printStrN();
            $this->printStrN('Available commands:', 'yellow');
            $handles = $this->beautify($handles, $command, 'cyan', ':', $commandOptions);
            foreach ($handles as $k => $v) {
                //if (is_string($v)) {
                $this->printStrN('  ' . $commandPrefix . ' ' . ltrim($v));
                //} else {
                //$commandOptions = ;
                //}
            }
        }
        if ($options) {
            $this->printStrN();
            $this->printStrN('Options:', 'yellow');
            foreach ($this->beautify($options, '', 'dark_gray') as $k => $v) {
                $this->printStrN($v);
            }
        }
        if (!!$commandOptions) {
            $this->printStrN();
            $this->printStrN('Commands options:', 'yellow');
            foreach ($commandOptions as $k => $v) {
                $this->printStrN('  ' . $k . '', 'purple');
                foreach ($this->beautify($v, '', 'dark_gray') as $kk => $vv) {
                    $this->printStrN(' ' . $vv);
                }
            }
        }
        if ($example) {
            $this->printStrN();
            $this->printStrN('Demo:', 'yellow', '');
            foreach (
                $this->beautify($example, z::arrayGet($args, 0) . ' ' . $command,
                    'cyan', false) as $k => $v
            ) {
                $this->printStrN($v);
            }
        }
    }

    /**
     * 命令配置.
     * @return array
     */
    abstract public function options();

    /**
     * 子命令.
     * @return array
     */
    public function commands()
    {
        return [];
    }

    final public function getHandle()
    {
        $keys = array_diff(get_class_methods($this),
            get_class_methods(__CLASS__));

        return array_fill_keys($keys, '');
    }

    /**
     * 命令示例.
     * @return array
     */
    public function example()
    {
        return [];
    }

    /**
     * 命令介绍.
     * @return string
     */
    abstract public function description();

    final public function beautify($commands, $command = '', $color = '', $pre = ' ', &$deploy = null)
    {
        $lists   = [];
        $isAssoc = function ($array) {
            if (is_array($array)) {
                $keys = array_keys($array);

                return join('|', $keys) != join('|', array_keys($keys));
            }

            return false;
        };
        $is      = $isAssoc($commands);
        $maxLen  = 10;
        $_tmp    = array_keys($commands);
        usort($_tmp, function ($e, $c) {
            return strlen($e) < strlen($c);
        });
        $len = strlen($_tmp[0]);
        if ($maxLen > $len) {
            $len = $maxLen;
        }
        foreach ($commands as $key => $value) {
            $cmd = str_pad($key, $len);
            if (!z::strBeginsWith($key, ' ') && !z::strBeginsWith($key, ':')) {
                if (false === $pre) {
                    $command = '';
                } else {
                    $cmd = $pre . $cmd;
                }
            }
            $m = $this->color($cmd, $color);
            if (is_array($value)) {
                if ($m) {
                    $k = trim($cmd);
                    if (count($value) > 1) {
                        $_k           = trim(z::strBeginsWith($k, ':') ? $command . $k : $k);
                        $deploy [$_k] = $value[1];
                    }
                }
                $value = $value[0];
            }
            $lists[] = !$is ? '  ' . $command . $value : '  ' . $command . $m . '  ' . $value;
        }

        return $lists;
    }

    /**
     * 命令默认执行.
     * @param $args
     * @return mixed
     */
    abstract public function execute($args);
}
