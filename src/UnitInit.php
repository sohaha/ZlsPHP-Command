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
        $this->batchCopy(__DIR__ . '/Unit/templates', Z::realPathMkdir('.', true, false, false));
        $this->success('Generate unit test templates success');
    }
}
