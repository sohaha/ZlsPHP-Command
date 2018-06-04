<?php

namespace Zls\Command;

use Z;

/**
 * 设置
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @link          ---
 * @since         v0.0.1
 * @updatetime    2018-02-01 15:01
 */
class Set extends Command
{
    public function description()
    {
        return 'Quick Set Config';
    }

    public function options()
    {
        return [
        ];
    }

    public function handle()
    {
        return [
            'release' => 'Optimize the formal environment configuration',
        ];
    }

    public function example()
    {
        return [
        ];
    }


    public function execute($args)
    {
        $method = z::arrayGet($args, ['type', 2]);
        if (method_exists($this, $method)) {
            $this->$method($args);
        } else {
            $this->help($args);
        }
    }

    public function release()
    {
        z::command('composer dump-autoload --optimize');
        /**
         * @var \Zls\Action\Ini $Ini
         */
        $Ini = z::extension('Action\Ini');
        $config = z::config('ini');
        $config = z::arrayMap($config, function ($v) {
            $config = [];
            foreach ($v as $k => $vv) {
                $config[$k] = ($k === 'debug') ? 0 : $vv;
            }

            return $config;
        });
        $status = @file_put_contents(ZLS_PATH . '../zls.ini', $Ini->extended($config));
        $this->printStrN('Modify zls.ini success', 'green');
    }
}
