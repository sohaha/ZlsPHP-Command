<?php

namespace Zls\Command;

use Z;

/**
 * 互动
 *
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
        return 'Shortcut command';
    }

    public function options()
    {
        return [
        ];
    }

    public function handle()
    {
        return [
            ' release' => 'Optimize the formal environment configuration',
            ' build'   => 'Project packaging phar',
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

    public function build()
    {
        $packageName = 'zls';
        $ext         = 'phar';
        $time        = date('YmdHis', time());
        $packageName = "{$packageName}.{$ext}";
        $buildPath   = Z::realPathMkdir('build/', false, false, false);
        $path        = ZLS_PATH.'../';
        $pharPath    = $path.$time.$packageName;
        try {
            $phar = new \Phar(
                $pharPath,
                0,
                $packageName
            );
            $phar->buildFromDirectory($path, '/\.php$/');
            $phar->buildFromDirectory($path, '/\.example$/');
            $phar->compressFiles(Phar::GZ);
            $phar->stopBuffering();
            $webIndex
                = "<?php
Phar::mapPhar('{$packageName}');
define('ZLS_PATH', 'phar://{$packageName}/');
define('ZLS_APP_PATH', 'phar://{$packageName}/application/');
define('ZLS_STORAGE_PATH', './storage');
require 'phar://{$packageName}/public/index.php';
__HALT_COMPILER();
?>";
            $phar->setStub(
                $webIndex
            );
            $this->success('build: '.$pharPath);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function release()
    {
        z::command('composer dump-autoload --optimize');
        /**
         * @var \Zls\Action\Ini $Ini
         */
        $Ini    = z::extension('Action\Ini');
        $config = z::config('ini');
        $config = z::arrayMap($config, function ($v) {
            $config = [];
            foreach ($v as $k => $vv) {
                $config[$k] = ($k === 'debug') ? 0 : $vv;
            }

            return $config;
        });
        $status = @file_put_contents(ZLS_PATH.'../zls.ini',
            $Ini->extended($config));
        $this->printStrN('Modify zls.ini success', 'green');
    }

}
