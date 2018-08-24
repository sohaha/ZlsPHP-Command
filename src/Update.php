<?php

namespace Zls\Command;

use Z;

/**
 * 更新.
 *
 * @author        影浅
 * @email         seekwe@gmail.com
 *
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 *
 * @see          ---
 * @since         v0.0.1
 * @updatetime    2018-02-01 15:01
 */
class Update extends Command
{
    const LOG_URL = 'http://zlsphp.tx.73zls.com/update.json';
    const FRAMEWORK_URL = 'https://raw.githubusercontent.com/sohaha/ZlsPHP/master/application/core/Zls.php';

    public function description()
    {
        return 'Update ZlsPHP Framework';
    }

    public function options()
    {
        return [];
    }

    public function commands()
    {
        return [
            'self' => 'Update Framework',
            'log' => 'Check the update log, do not update',
        ];
    }

    public function example()
    {
        return [];
    }

    public function self()
    {
        $this->log(true);
    }

    public function log($download = false)
    {
        $version = IN_ZLS;
        /**
         * @var \Zls\Action\Http
         */
        $ActionHttp = z::extension('Action\Http');
        $updateLog = $ActionHttp->get(self::LOG_URL, null, null, 1);
        $updateLog = @json_decode($updateLog, true) ?: [];
        $updateLog = z::arrayFilter($updateLog, function ($e, $k) use ($version) {
            return $k > $version;
        });
        if ($updateLog) {
            $this->download();
            $log = ['Update Log'.PHP_EOL];
            foreach ($updateLog as $v => $t) {
                $this->printStrN("v{$v}:", 'white');
                $this->printStrN($t, 'light_gray');
                $this->printStrN();
            }
        } else {
            $this->success('Already the latest version, No need to update.', 'green');
        }
    }

    protected function download()
    {
        /**
         * @var \Zls\Action\Http
         */
        $ActionHttp = z::extension('Action\Http');
        $content = $ActionHttp->get(self::FRAMEWORK_URL, null, null, 1);
        $savePath = z::tempPath().'/Zls.php';
        if (strlen($content) > 500 && @file_put_contents($savePath, $content)) {
            rename($savePath, ZLS_FRAMEWORK);
        }
    }

    public function execute($args)
    {
        return $this->help($args);
    }
}
