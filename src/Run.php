<?php

namespace Zls\Command;

use Z;

/**
 * 互动.
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @see           ---
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

    public function commands()
    {
        return [
            ' release' => 'Optimize the formal environment configuration',
            ' build'   => [
                'Project packaging phar',
                ['-o, -O, --output' => 'Custom output directory'],
            ],
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

    public function extract($args)
    {
        if (!$file = Z::arrayGet($args, ['-file', 'F'])) {
            $this->error('Phar file cannot be empty');
        } else {
            $path = Z::realPathMkdir('extract', true, false, false);
            z::rmdir($path, false);
            $phar = new \Phar(Z::realPath($file, false, false));
            $phar->extractTo($path);
            $this->success('extract -> ' . $path);
        }
    }

    public function build($args)
    {
        $name        = 'zls';
        $ext         = '.phar';
        $time        = date('YmdHis');
        $packageName = "{$name}{$ext}";
        $buildPath   = Z::realPathMkdir(Z::arrayGet($args, ['o', 'O', '-output'], 'build/'), true, false, false);
        $path        = ZLS_PATH . '../';
        $pharPath    = $buildPath . $packageName;
        try {
            @unlink($pharPath);
            $phar    = new \Phar(
                $pharPath,
                0,
                $packageName
            );
            $exclude = '/^(?!(.*build|.*extract|.*storage))(.*)\.php$/i';
            $phar->buildFromDirectory($path, $exclude);
            $phar->buildFromDirectory($path, '/^zls\.ini\.example$/');
            $phar->compressFiles(\Phar::GZ);
            $phar->stopBuffering();
            $app = str_replace(Z::realPath(getcwd()), '', Z::realPath(ZLS_APP_PATH));
            $webIndex
                 = "<?php
Phar::mapPhar('{$packageName}');
defined('ZLS_PATH') || define('ZLS_PATH', 'phar://{$packageName}/');
defined('ZLS_APP_PATH') || define('ZLS_APP_PATH', 'phar://{$packageName}{$app}/');
defined('ZLS_STORAGE_PATH') || define('ZLS_STORAGE_PATH', getcwd().'/../storage/');
require 'phar://{$packageName}/public/index.php';
__HALT_COMPILER();
";
            $phar->setStub(
                $webIndex
            );
            $publicPath = Z::realPathMkdir($buildPath . 'public', true);
            $this->copyDir(Z::realPath('./'), $publicPath);
            $iniFile = Z::realPath('zls.ini.example', false, false);
            if (is_file($iniFile)) {
                copy($iniFile, $buildPath . 'zls.ini.example');
            }
            file_put_contents($publicPath . 'index.php', "<?php
/**
 * Zls
 * @author        影浅 <seekwe@gmail.com>
 * @see           https://docs.73zls.com/zls-php/#
 */
defined('ZLS_PATH') || define('ZLS_PATH', __DIR__ . '/');
defined('ZLS_INDEX_NAME') || define('ZLS_INDEX_NAME', pathinfo(__FILE__, PATHINFO_BASENAME));
defined('ZLS_STORAGE_PATH') || define('ZLS_STORAGE_PATH', ZLS_PATH. '/../storage/');
require __DIR__.'/../{$packageName}';");
            $this->success('build -> ' . z::realpath($pharPath));
        } catch (\Exception $e) {
            Z::rmdir($buildPath);
            $this->error($e->getMessage());
        }
    }

    private function copyDir($dirSrc, $dirTo)
    {
        if (is_file($dirTo)) {
            return $dirTo . '不是一个目录';
        }
        if (!file_exists($dirTo)) {
            mkdir($dirTo);
        }
        if ($handle = opendir($dirSrc)) {
            while ($filename = readdir($handle)) {
                if ('.' != $filename && '..' != $filename) {
                    $subsrcfile = $dirSrc . '/' . $filename;
                    $subtofile  = $dirTo . '/' . $filename;
                    if (is_dir($subsrcfile)) {
                        $this->copyDir($subsrcfile, $subtofile); //再次递归调用copydir
                    }
                    if (is_file($subsrcfile)) {
                        if (z::realPath($subsrcfile) !== z::realPath(ZLS_PATH
                                . ZLS_INDEX_NAME)
                        ) {
                            copy($subsrcfile, $subtofile);
                        }
                    }
                }
            }
            closedir($handle);
        }

        return true;
    }

    public function release()
    {
        z::command('composer dump-autoload --optimize');
        /**
         * @var \Zls\Action\Ini
         */
        $Ini    = z::extension('Action\Ini');
        $config = z::config('ini');
        $config = z::arrayMap($config, function ($v) {
            $config = [];
            foreach ($v as $k => $vv) {
                $config[$k] = ('debug' === $k) ? 0 : $vv;
            }

            return $config;
        });
        $status = @file_put_contents(ZLS_PATH . '../zls.ini',
            $Ini->extended($config));
        $this->printStrN('Modify zls.ini success', 'green');
    }
}
