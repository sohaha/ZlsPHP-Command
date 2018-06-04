<?php

namespace Zls\Command;

use Z;

/**
 * Artisan
 * @author        影浅-Seekwe
 * @email       seekwe@gmail.com
 * @updatetime    2017-2-27 16:52:51
 */
class Main extends Command
{
    private $args = [];

    private $command = [
        'set'    => '\Zls\Command\Set',
        'start'  => '\Zls\Command\Start',
        'create' => '\Zls\Command\Create',
        'mysql'  => '\Zls\Command\Mysql',
    ];

    public function title()
    {
    }

    public function options()
    {
    }

    public function example()
    {
    }

    public function description()
    {
    }

    public function execute($args)
    {
        if (z::arrayGet($args, ['color', 'c'])) {
            $this->getColor();
            z::finish();
        }
        $commandRun = z::arrayGet($args, 0);
        $this->printStrN('ZlsPHP command lists', 'light_green');
        $this->printStrN();
        $this->printStrN('Usage:', 'yellow');
        $this->printStrN(
            '  ' .
            $commandRun .
            ' ' . $this->color('{command}', 'green') .
            ' ' . $this->color('[arg1 value1 args2=value2 ...] [Options]', 'dark_gray')
        );
        $this->printStrN();
        $this->printStrN('Options:', 'yellow');
        $this->printStrN('  ' . $this->color('-h, -help', 'green') . '    Show command help');
        $this->printStrN('  ' . $this->color('-c, -color', 'green') . '   Show Color name');
        $files = Z::scanFile(ZLS_APP_PATH . 'classes/Command', 99, function ($dir, $name) {
            if (is_dir($dir . '/' . $name)) {
                return true;
            } else {
                return Z::strEndsWith(strtolower(pathinfo($name, PATHINFO_EXTENSION)), 'php');
            }
        });
        $this->printStrN();
        $this->printStrN('BuiltIn Command:', 'yellow');
        $this->getInfo($this->command);
        $this->printStrN();
        $commandFile = [];
        $this->getClassName($commandFile, $files);
        $extendCommand = array_merge($commandFile, Z::config()->getCommands());
        if ($extendCommand) {
            $this->printStrN('Extend Command:', 'yellow');
            $this->getInfo(array_diff($extendCommand, $this->command));
        }
        $this->printStrN();
        $this->printStrN('More command information, please use: ' . $commandRun . ' ' . $this->color('{command}', 'green') . ' -h');
    }

    private function getColor()
    {
        $fgs = $this->getColors();
        $bgs = $this->getBgColors();
        foreach ($fgs as $i => $v) {
            echo $this->printStr(str_pad($i, 10, ' '), $i) . "\t";
            if (isset($bgs[$i])) {
                $this->printStr($i, null, $i);
            }
            echo PHP_EOL;
        }
        echo PHP_EOL;
        foreach ($fgs as $fg => $v) {
            foreach ($bgs as $bg => $vv) {
                $this->printStrN(str_pad("Text:{$fg}+Bg:{$bg}", 50, ' '), $fg, $bg);
            }
        }
    }

    private function getInfo($commands)
    {
        $lists = [];
        $maxLen = 10;
        foreach ($commands as $key => $value) {
            /** @var Command $command */
            $command = Z::factory($value);
            $lists[$key] = $command->description();
            $len = strlen($key);
            if ($len > $maxLen) {
                $maxLen = $len;
            }
        }
        foreach ($lists as $key => $list) {
            $this->printStrN(
                '  ' .
                $this->color(str_pad($key, $maxLen), 'green') .
                '   ' .
                $list
            );
        }
    }

    private function getClassName(&$list, $files, $prefix = '')
    {
        $prefix = $prefix ? $prefix . '\\' : '';
        foreach ($files as $k => $v) {
            if ($k === 'file') {
                foreach ($v as $name) {
                    $name = str_replace('.' . strtolower(pathinfo($name, PATHINFO_EXTENSION)), '', $name);
                    $list[$prefix . $name] = '\\Command\\' . $prefix . $name;
                }
            } else {
                foreach ($v as $_k => $name) {
                    $this->getClassName($list, $name, $prefix . $_k);
                }
            }
        }
    }
}
