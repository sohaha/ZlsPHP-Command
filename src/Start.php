<?php

namespace Zls\Command;

use Z;

/**
 * 本地服务器
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @link          ---
 * @since         v0.0.1
 * @updatetime    2018-02-01 15:01
 */
class Start extends Command
{
    public function execute($args)
    {
        $port = z::arrayGet($args, ['port', 'P', 3], 3780);
        $ip = z::arrayGet($args, ['host', 'H'], '127.0.0.1');
        $url = $ip . ':' . $port;
        $cmd = z::phpPath() . ' -S ' . $url . ' -t ' . z::realPath(ZLS_PATH);
        if (file_exists($filePath = __DIR__ . '/StartRun.php')) {
            $cmd .= ' -file ' . $filePath;
        }
        if ($ip === '0.0.0.0') {
            $url = z::serverIp() . ':' . $port;
        }
        echo $this->strN("HttpServe: http://{$url}", 'white', 'red') . PHP_EOL;
        try {
            echo z::command($cmd);
        } catch (\Zls_Exception_500 $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    public function description()
    {
        return 'Quick Start web server';
    }

    public function options()
    {
        return [
            '-host, -H' => 'Listening IP',
            '-port, -P' => 'Listening Port',
        ];
    }

    public function example()
    {
        return [
            ' -host 0.0.0.0' => 'To make the network access',
        ];
    }

}
