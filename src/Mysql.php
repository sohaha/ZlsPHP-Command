<?php

namespace Zls\Command;

use Z;

/**
 * mysql执行
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @link          ---
 * @since         v0.0.1
 * @updatetime    2018-02-01 15:01
 */
class Mysql extends Command
{
    private $dir = '../database/mysql';
    private $prefix = 'Command_';

    public function description()
    {
        return 'Mysql Backup And Restore';
    }

    public function handle()
    {
        return [
            'import' => 'Import backup file',
            'export' => 'Export backup file',
        ];
    }

    public function options()
    {
        return [
            '--filename' => 'Database filePath',
            '--backup'   => 'Import the old backup data',
            '--ignore'   => 'Export the ignore tableNames, Multiple comma separated',
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

    public function import($args)
    {
        $filePath = z::arrayGet($args, '-filename');
        $backup = z::arrayGet($args, '-backup', true);
        $tablePrefix = z::tap(\explode(':', z::arrayGet($args, '-prefix', '')), function ($prefix) {
            return (count($prefix) < 2) ? false : $prefix;
        });
        $dbExist = true;
        /**
         * @var \Zls\Command\Mysql\MysqlEI $MysqlEI
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
                $sql = 'CREATE DATABASE ' . $database;
                $master = z::tap($db->getMasters(), function ($master) {
                    return end($master);
                });
                try {
                    $pdo = new \Zls_PDO('mysql:host=' . z::arrayGet($master, 'hostname1') . ';port=' . z::arrayGet($master, 'port') . ';dbname=mysql;charset=' . z::arrayGet($config, 'charset'), z::arrayGet($master, 'username'), z::arrayGet($master, 'password'));
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    $pdo->exec($sql);
                } catch (\Exception $exc) {
                    z::throwIf(true, 'Database', $sql . ' Error, Please manually create the database');
                }
                $MysqlEI = z::extension('Command\Mysql\MysqlEI');
            }
            if ($dbExist && $backup) {
                $allTable = $MysqlEI->allTable();
                if (\count($allTable) > 0) {
                    echo 'Database exists, create a backup' . PHP_EOL;
                    try {
                        $msg = $MysqlEI->export(null, '', 'Backup_' . $this->prefix);
                        foreach ($msg as $v) {
                            echo $v . PHP_EOL;
                        }
                    } catch (\Exception $exc) {
                        echo $exc->getMessage() . PHP_EOL;
                    }
                }
            }
            $date = 0;
            if (!$filePath) {
                if ($dh = opendir(z::realPathMkdir($this->dir))) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file != "." && $file != ".." && preg_match('/^' . $this->prefix . '(\d+)_(.*)/', $file, $volume)) {
                            $newDate = $volume[1];
                            if ($newDate > $date) {
                                $date = $newDate;
                                $filePath = $file;
                            }
                        }
                    }
                }
            }
            $MysqlEI->import(z::realPath($this->dir . '/' . $filePath), $tablePrefix);
        } catch (\Exception $exc) {
            echo $exc->getMessage() . PHP_EOL;
        }
    }


    public function export($args)
    {
        $table = z::arrayGet($args, '-table');
        $filename = z::arrayGet($args, '-filename');
        $size = z::arrayGet($args, '-size', 1024);
        if ($dir = z::arrayGet($args, '-dir')) {
            $dir = z::realPathMkdir($dir, true);
        }
        if ($ignoreData = z::arrayGet($args, '-ignore')) {
            $_ignoreData = [];
            foreach (\explode(',', $ignoreData) as $v) {
                $v = \explode(':', $v);
                if ((int)z::arrayGet($v, 1) === 1) {
                    $_ignoreData[$v[0]] = false;
                } else {
                    $_ignoreData[$v[0]] = true;
                }
            }
            $ignoreData = $_ignoreData;
        }
        try {
            /**
             * @var \Zls\Command\Mysql\MysqlEI $MysqlEI
             */
            $MysqlEI = Z::extension('Command\Mysql\MysqlEI');
            $this->printStrN('Start backup, please wait', 'light_blue');
            $MysqlEI->export($table, $dir, $this->prefix, $ignoreData, $filename, $size);
        } catch (\Exception $exc) {
            echo $exc->getMessage() . PHP_EOL;
        }
    }
}
