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
        if (Z::arrayGet($args, ['H'])) {
            foreach ($_SERVER['argv'] as &$v) {
                if ($v === "-H") {
                    $v = '--help';
                    break;
                }
            }
        }
        $command = Z::arrayGet($args, 0) . ' ' . Z::arrayGet($args, 1);
        $this->warning("Please use {$command} instead of phpunit.\n");
        $this->printStr('Usage:', 'yellow');
        $this->printStrN("  {$command} [options] UnitTest [UnitTest.php]\n");
        $this->run($args);
    }

    private function run($args)
    {
        $options = getopt('', ['prepend:']);
        if (isset($options['prepend'])) {
            /** @noinspection PhpIncludeInspection */
            require $options['prepend'];
        }
        unset($options);
        if (!class_exists('\PHPUnit\TextUI\Command')) {
            $this->error("Please install the unit test package!\nInstall Command: composer require --dev zls/unit");

            return;
        }
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
}
