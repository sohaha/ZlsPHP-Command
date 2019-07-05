<?php

namespace Zls\Command;

use Z;

/**
 * Unit Init
 * @author        影浅 <seekwe@gmail.com>
 */
class UnitInit extends Command
{

    /**
     * 命令配置.
     * @return array
     */
    public function options()
    {
        return [];
    }

    /**
     * 命令介绍.
     * @return string
     */
    public function description()
    {
        return 'Generate unit test templates';
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
        if (!Z::arrayGet((new Main())->getBuiltInCommand(), 'unitInit')) {
           $this->error("Please install the unit test package!\nInstall Command: composer require --dev zls/unit", '', true);
        }
        $this->batchCopy(__DIR__ . '/../../unit/src/Templates', Z::realPathMkdir('.', true, false, false), false, function ($dest, $file) {
            return $this->destPathProcess($dest, $file);
        });
        $this->success('Generate unit test templates success');
    }

    private function destPathProcess($dest, $file)
    {
        $dest = str_replace('php.template', 'php', $dest);

        return $dest;
    }
}
