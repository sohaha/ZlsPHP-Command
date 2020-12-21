<?php

namespace Zls\Command;

use Z;

/**
 * 互动
 */
class Run extends Command
{
    public function description()
    {
        return 'Shortcut command';
    }

    public function options()
    {
        return [];
    }

    public function commands()
    {
        return [
            ' release' => 'Optimize the formal environment configuration',
            ' build' => [
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
        $name = 'zls';
        $ext = '.phar';
        $packageName = "{$name}{$ext}";
        $buildPath = Z::realPathMkdir(Z::arrayGet($args, ['o', 'O', '-output'], 'build/'), true, false, false);
        $suffix = Z::arrayGet($args, ['s', '-suffix'], 'php,html,js,css,vue,json');
        $suffix = join('|', explode(',', $suffix));
        if (!$suffix) {
            $suffix = 'php';
        }
        $path = Z::realPath(ZLS_PATH . '..', true);
        $pharPath = $buildPath . $packageName;

        try {
            @unlink($pharPath);
            $phar = new \Phar(
                $pharPath,
                0,
                $packageName
            );
            $d = str_replace('/', '\/', $path);
            $exclude = '/^(?!(.*build|.*extract|.*storage|' . $d . 'public|' . $d . 'database))(.*)\.(' . $suffix . ')/i';
            $phar->buildFromDirectory($path, $exclude);
            foreach (['public/index.php'] as $v) {
                $phar->addFile(Z::realPath($path . $v), $v);
            }
            $phar->compressFiles(\Phar::GZ);
            $phar->stopBuffering();
            $app = str_replace(Z::realPath("../"), '', Z::realPath(ZLS_APP_PATH));
            $webIndex
                = "<?php
Phar::mapPhar('{$packageName}');
define('ZLS_PHAR_PATH','phar://zls.phar/');
defined('ZLS_PATH') || define('ZLS_PATH', 'phar://{$packageName}/');
defined('ZLS_APP_PATH') || define('ZLS_APP_PATH', 'phar://{$packageName}{$app}');
defined('ZLS_STORAGE_PATH') || define('ZLS_STORAGE_PATH', getcwd().'/../storage/');
require 'phar://{$packageName}/public/index.php';
__HALT_COMPILER();
";
            $phar->setStub(
                $webIndex
            );
            $publicPath = Z::realPathMkdir($buildPath . 'public', true);
            $databasePath = Z::realPath('database', true, false);
            $this->copyDir(Z::realPath('./'), $publicPath);
            if (is_dir($databasePath)) {
                $this->copyDir($databasePath, Z::realPathMkdir($buildPath . 'database', true));
            }
            $iniFile = Z::realPath('zls.ini.example', false, false);
            if (is_file($iniFile)) {
                copy($iniFile, $buildPath . 'zls.ini.example');
            }
            file_put_contents($buildPath . 'zls', "#!/usr/bin/env php
<?php require __DIR__ . '/public/index.php';");
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
            /** @noinspection PhpUndefinedConstantInspection */
            $storage = Z::realPathMkdir(Z::safePath(ZLS_STORAGE_PATH, $buildPath), true);
            file_put_contents($storage . '.gitkeep', '*');
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
                    $subtofile = $dirTo . '/' . $filename;
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
        $Ini = z::extension('Action\Ini');
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
