<?php

namespace Zls\Command;

use Z;

/**
 * Artisan.
 * @author        影浅-Seekwe <seekwe@gmail.com>
 */
class Main extends Command
{
    private $args = [];

    private $command = [];

    public function __construct()
    {
        parent::__construct();
        $command = [
            //'set'    => '\Zls\Command\Set',
            //'update' => '\Zls\Command\Update',
            'run' => '\Zls\Command\Run',
        ];
        $unitCommand = ['unit' => '\Zls\Command\Unit'];
        if (class_exists('Zls\Unit\Templates')) {
            $unitCommand['unitInit'] = '\Zls\Command\UnitInit';
        }
        $this->command = array_merge($command, $unitCommand, [
            'start' => '\Zls\Command\Start',
            'tasks' => '\Zls\Command\Tasks',
            'create' => '\Zls\Command\Create',
            'mysql' => '\Zls\Command\Mysql',
        ]);
    }

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
        if (z::arrayGet($args, ['-color', 'C'])) {
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
            ' ' . $this->color('[arg1 value1 args2=value2 args3 ...] [Options]',
                'dark_gray')
        );
        $this->printStrN();
        $this->printStrN('Options:', 'yellow');
        $this->printStrN('  ' . $this->color('-H, --help', 'green')
            . '    Show command help');
        $this->printStrN('  ' . $this->color('-C, --color', 'green')
            . '   Show Color name');
        $files = Z::scanFile(ZLS_APP_PATH . 'classes/Command', 99,
            function ($dir, $name) {
                if (is_dir($dir . '/' . $name)) {
                    return true;
                } else {
                    return Z::strEndsWith(strtolower(pathinfo($name,
                        PATHINFO_EXTENSION)), 'php');
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
        $this->printStrN('More command information, please use: ' . $commandRun
            . ' ' . $this->color('{command}', 'green') . ' -H');
    }

    public function getBuiltInCommand()
    {
        return $this->command;
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
                $this->printStrN(str_pad("Text:{$fg}+Bg:{$bg}", 50, ' '), $fg,
                    $bg);
            }
        }
    }

    private function getInfo($commands)
    {
        $lists = [];
        $maxLen = 10;
        $errs = [];
        foreach ($commands as $key => $value) {
            try {
                /** @var Command $command */
                $command = Z::factory($value);
                $lists[$key] = $command->description();
                $len = strlen($key);
                if ($len > $maxLen) {
                    $maxLen = $len;
                }
            } catch (\Zls_Exception_500 $e) {
                $errs[] = $e->getErrorMessage();
            }
        }
        foreach ($lists as $key => $list) {
            $this->printStrN(
                '  ' .
                $this->color(str_pad(lcfirst($key), $maxLen), 'green') .
                '   ' .
                $list
            );
        }
        foreach ($errs as $err) {
            $this->printStrN();
            $this->warning($err);
        }
    }

    private function getClassName(&$list, $files, $prefix = '')
    {
        $prefix = $prefix ? $prefix . '\\' : '';
        foreach ($files as $k => $v) {
            if ('file' === $k) {
                foreach ($v as $name) {
                    $name = str_replace('.'
                        . strtolower(pathinfo($name, PATHINFO_EXTENSION)), '',
                        $name);
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
