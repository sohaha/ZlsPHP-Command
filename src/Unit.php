<?php

namespace Zls\Command;

use Z;

/**
 * Unit
 * @author        影浅 <seekwe@gmail.com>
 */
class Unit extends Command
{

    /**
     * 命令配置.
     * @return array
     */
    public function options()
    {
        return [
            '--help' => 'View phpunit help documentation',
        ];
    }

    /**
     * 命令介绍.
     * @return string
     */
    public function description()
    {
        return 'Execution unit test';
    }

    /**
     * 命令默认执行.
     *
     * @param $args
     *
     * @return void
     */
    public function execute($args)
    {
        if (!is_dir(Z::realPath('../tests'))) {
            $this->error("No 'tests' directory found, please initialize the template\nCommand: php zls unitInit");

            return;
        }
        $this->run($args);
    }

    public function help($args, $command = null)
    {
        $this->run($args);
    }

    private function run($args)
    {
        if (!class_exists('\PHPUnit\TextUI\Command')) {
            $this->error("Please install the unit test package!\nInstall Command: composer require --dev zls/unit");

            return;
        }
        $argv = Z::arrayGet($GLOBALS, 'argv', []);
        array_shift($argv);
        array_shift($argv);
        if (!Z::arrayGet($args, '--configuration')) {
            $argv[] = '--configuration';
            $argv[] = Z::realPath('phpunit.xml', false, false);
        }
        // if ($this->ansiColorsSupported()) {
        if (!Z::arrayGet($args, '--colors')) {
            $argv[] = '--colors=always';
        }
        // }
        $phpunitPath = $this->getPhpunit();
        if (!$this->getPhpunit()) {
            if (class_exists("\PHPUnit\TextUI\Command")) {
                $this->warning("请安装phpunit.phar获取更好的体验!");
                $this->phpunit();
            } else {
                $this->error("Please install the phpunit.phar!");
            }
            $this->success("安装命令");

            return;
        }
        $cmd = Z::phpPath() . ' ' . $phpunitPath . " " . join(" ", $argv);
        $res = Z::command($cmd);
        echo $res;
    }

    private function phpunit()
    {
        $options = getopt('', ['prepend:']);
        if (isset($options['prepend'])) {
            /** @noinspection PhpIncludeInspection */
            require $options['prepend'];
        }
        unset($options);
        if (!isset($GLOBALS['__PHPUNIT_ISOLATION_BLACKLIST'])) {
            $GLOBALS['__PHPUNIT_ISOLATION_BLACKLIST'] = [];
        }
        $GLOBALS['__PHPUNIT_ISOLATION_BLACKLIST'] = array_merge($GLOBALS['__PHPUNIT_ISOLATION_BLACKLIST'], [
            ZLS_CORE_PATH,
            __FILE__,
            ZLS_PATH . ZLS_INDEX_NAME,
        ]);
        unset($_SERVER['argv'][1]);
        $_SERVER['argv'] = array_values($_SERVER['argv']);
        \PHPUnit\TextUI\Command::main();
    }

    private function getPhpunit()
    {
        if (Z::isWin()) {
            $cmd       = 'echo %PATH%';
            $delimiter = ":";
        } else {
            $cmd       = 'echo $PATH';
            $delimiter = ":";
        }
        $phpunitName = ["/phpunit", "/phpunit.phar", "/phpunit-8.phar"];
        $paths       = explode($delimiter, Z::command($cmd, null, true, false));
        $paths[]     = Z::realPath(".", false, false);
        foreach ($paths as $path) {
            foreach ($phpunitName as $name) {
                $phpunitPath = $path . $name;
                if (is_file($phpunitPath)) {
                    return $phpunitPath;
                }
            }
        }

        return "";
    }
}
