<?php

namespace Zls\Command;

use Z;

/**
 * 互动
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @link          ---
 * @since         v0.0.1
 * @updatetime    2018-02-01 15:01
 */
class Run extends Command
{
    public function description()
    {
        return 'Interactive shell';
    }

    public function options()
    {
        return [
        ];
    }

    public function handle()
    {
        return [
        ];
    }

    public function example()
    {
        return [
        ];
    }


    public function execute($args)
    {
        $cmd = z::phpPath() . ' -a ';
        try {
            echo z::command($cmd,null,false);
        } catch (\Zls_Exception_500 $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }


}
