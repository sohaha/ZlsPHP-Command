<?php

namespace Zls\Command;

use Z;

/**
 * mysql执行.
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
class Mysql extends Command
{
    const PREFIX = 'Command_';
    private $dir;
    private $filename;
    private $backup;
    private $tablePrefix;
    private $table;
    private $size;
    private $includeData;
    private $ignoreData;

    public function description()
    {
        return 'Mysql Backup And Restore';
    }

    public function commands()
    {
        return [
            'import' => 'Import backup file',
            'export' => 'Export backup file',
        ];
    }

    public function example()
    {
        return [
            'export -N test -N log:1' => 'Export Database, ignore table "test" And ignore table "log" Data',
        ];
    }

    public function options()
    {
        return [
            '-F, --filename' => 'Database filePath',
            '-B, --backup' => 'Import the old backup data',
            '-D, --dir' => 'Database data directory',
            //'-P, --prefix'   => 'Table prefix, old:new',
            '-N, --ignore' => 'Export the ignore tableNames',
            '-I, --include' => 'Export the include tableNames. (Not required)',
        ];
    }

    public function execute($args)
    {
        $argc = z::getOpt();
        $dir = z::arrayGet($argc, ['-dir', 'D']);
        $this->dir = (is_string($dir)) ? $dir : z::realPath('database/mysql', true, false);
        $this->dir = z::realPathMkdir($this->dir, true, false, false);
        $this->filename = z::arrayGet($argc, ['-filename', 'F']);
        $this->backup = z::arrayGet($argc, ['-backup', 'B'], true);
        $table = z::arrayGet($argc, ['-table', 'T'], true);
        $this->table = (is_string($table)) ? $table : null;
        $this->size = z::arrayGet($argc, ['-size'], 1024);
        $this->includeData = z::arrayGet($args, ['-include', 'I'], [], false, ',');
        $this->ignoreData = z::arrayGet($args, ['-ignore', 'N'], [], false, ',');
        $this->tablePrefix = z::tap(explode(':', z::arrayGet($argc, ['-prefix', 'P'], '')), function ($prefix) {
            return (count($prefix) < 2) ? false : $prefix;
        });
        $method = z::arrayGet($args, ['type', 2]);
        if (method_exists($this, $method)) {
            $this->$method($args);
        } else {
            $this->help($args);
        }
    }

    public function import()
    {
        $filePath = $this->filename;
        $backup = $this->backup;
        $tablePrefix = $this->tablePrefix;
        $dbExist = true;
        /**
         * @var \Zls\Command\Mysql\MysqlEI
         */
        $MysqlEI = null;
        try {
            try {
                $MysqlEI = z::extension('Command\Mysql\MysqlEI');
            } catch (\Exception $exc) {
                $errMsg = $exc->getMessage();
                z::throwIf(!preg_match('/Database Group(.*)Unknown database(.*)/', $errMsg), 'Database', $errMsg);
                //数据库找不到,新建立一个
                $dbExist = false;
                $db = z::db();
                $config = $db->getConfig();
                $database = $config['database'];
                $sql = 'CREATE DATABASE '.$database;
                $master = z::tap($db->getMasters(), function ($master) {
                    return end($master);
                });
                try {
                    $pdo = new \Zls_PDO('mysql:host='.z::arrayGet($master, 'hostname1').';port='.z::arrayGet($master, 'port').';dbname=mysql;charset='.z::arrayGet($config, 'charset'), z::arrayGet($master, 'username'), z::arrayGet($master, 'password'));
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    $pdo->exec($sql);
                } catch (\Exception $exc) {
                    z::throwIf(true, 'Database', $sql.' Error, Please manually create the database');
                }
                $MysqlEI = z::extension('Command\Mysql\MysqlEI');
            }
            if ($dbExist && $backup) {
                $allTable = $MysqlEI->allTable();
                if (count($allTable) > 0) {
                    $this->printStrN('Database exists, create a backup');
                    try {
                        $msg = $MysqlEI->export(null, $this->dir, 'Backup_'.self::PREFIX);
                        if ($msg) {
                            $this->success('Database done.');
                        }
                    } catch (\Exception $exc) {
                        echo $exc->getMessage().PHP_EOL;
                    }
                }
            }
            $date = 0;
            if (!$filePath) {
                if ($dh = opendir(z::realPathMkdir($this->dir))) {
                    while (false !== ($file = readdir($dh))) {
                        if ('.' != $file && '..' != $file && preg_match('/^'.self::PREFIX.'(\d+)_(.*)/', $file, $volume)) {
                            $newDate = $volume[1];
                            if ($newDate > $date) {
                                $date = $newDate;
                                $filePath = $file;
                            }
                        }
                    }
                }
            }
            $MysqlEI->import(z::realPath($this->dir.'/'.$filePath), $tablePrefix);
        } catch (\Exception $exc) {
            echo $exc->getMessage().PHP_EOL;
        }
    }

    public function export($args)
    {
        $table = $this->table;
        $filename = $this->filename;
        $size = $this->size;
        $dir = $this->dir;
        if ($ignoreData = $this->ignoreData) {
            $_ignoreData = [];
            foreach (explode(',', $ignoreData) as $v) {
                $v = explode(':', $v);
                if (1 === (int) z::arrayGet($v, 1)) {
                    $_ignoreData[$v[0]] = false;
                } else {
                    $_ignoreData[$v[0]] = true;
                }
            }
            $ignoreData = $_ignoreData;
        }
        if ($includeData = $this->includeData) {
            $_includeData = [];
            foreach (explode(',', $includeData) as $v) {
                $v = explode(':', $v);
                if (1 === (int) z::arrayGet($v, 1)) {
                    $_includeData[$v[0]] = false;
                } else {
                    $_includeData[$v[0]] = true;
                }
            }
            $includeData = $_includeData;
        }
        try {
            /**
             * @var \Zls\Command\Mysql\MysqlEI
             */
            $MysqlEI = Z::extension('Command\Mysql\MysqlEI');
            $this->printStrN('Start backup, please wait', 'light_blue');
            $MysqlEI->export($table, $dir, self::PREFIX, $ignoreData, $includeData, $filename, $size);
        } catch (\Exception $exc) {
            echo $exc->getMessage().PHP_EOL;
        }
    }
}
