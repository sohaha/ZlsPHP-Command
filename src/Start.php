<?php

namespace Zls\Command;

use Z;

/**
 * 本地服务器.
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @see           ---
 * @since         v0.0.1
 * @updatetime    2018-02-01 15:01
 */
class Start extends Command
{
    public function description()
    {
        return 'Quick Start Web Server';
    }

    public function options()
    {
        return [
            '-I, -i, --host <host>' => 'Listening IP',
            '-P, -p, --port <port>' => 'Listening Port',
            '-C,     --external' => 'Open extranet access and ignore the --host setting',
        ];
    }

    public function commands()
    {
        return [];
    }

    public function example()
    {
        return [
            ' --host 0.0.0.0' => 'To make the network access',
            ' -P 8080' => 'Listening 8080 Port',
        ];
    }

    public function port()
    {
        $port = $this->ask('端口: ', null, '请输入端口: ');
        $this->execute(['-port' => $port]);
    }

    public function execute($args)
    {
        $port = (int)z::arrayGet($args, ['-port', 'port', 'P', 3], 3780);
        $host = z::arrayGet($args, ['-host', 'host', 'I'], '127.0.0.1');
        $newPort = $this->checkPortBindable($host, $port);
        if ($port !== $newPort) {
            $this->printStrN(
                "Warn Port {$port} has been used, switched to {$newPort}.",
                             'yellow'
            );
            $port = $newPort;
        }
        if (z::arrayGet($args, ['-external', 'C'])) {
            $host = '0.0.0.0';
        }
        $url = $host . ':' . $port;
        $zlsPath = z::realPath(ZLS_PATH);
        $cmd = z::phpPath() . ' -S ' . $url . ' -t ' . (z::strBeginsWith(
            $zlsPath,
                                                                         'phar://'
        ) ? getcwd() : $zlsPath);
        if (file_exists($filePath = __DIR__ . '/Start/StartRun.php')) {
            $cmd .= ' -file ' . $filePath;
        }
        if ('0.0.0.0' === $host) {
            $url = z::serverIp() . ':' . $port;
        }
        $this->printStrN($this->color(' Local ', 'white', 'blue') . " http://{$url}", 'white');
        try {
            echo z::command($cmd);
        } catch (\Zls_Exception_500 $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    private function checkPortBindable($host, $port, $numberOfRetries = 1000, $rawPort = null)
    {
        if (is_null($rawPort)) {
            $rawPort = $port;
        }
        $socket = @stream_socket_server("tcp://{$host}:{$port}");
        if (!$socket) {
            ++$port;
            --$numberOfRetries;

            return $numberOfRetries >= 0 ? $this->checkPortBindable($host, $port, $numberOfRetries, $rawPort) : $rawPort;
        }
        @fclose($socket);

        return $port;
    }
}
