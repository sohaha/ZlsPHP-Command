<?php

namespace Zls\Command;

use z;

/**
 * Create File
 */
class Create extends Command
{
    const CREATE_CLASS_NAME = 'Zls\Command\Create\Common';
    const TYPES = ['controller', 'business', 'command', 'model', 'task', 'dao', 'bean', 'unit'];

    private $args = [];

    public function description()
    {
        return 'Code Factory';
    }

    public function options()
    {
        return [
            '--type' => 'Create type [' . join(', ', self::TYPES) . ']',
            '--db' => 'Database Config Name',
            '--env' => 'Environment',
            '--force' => 'Overwrite old files',
            '--hmvc' => 'Hmvc Name',
        ];
    }

    public function example()
    {
        return [
            'controller      php zls create controller {filename}',
            'business        php zls create business {filename}',
            'command         php zls create command {filename}',
            'task            php zls create task {filename}',
            'dao             php zls create dao {filename} {tableName}',
            'bean            php zls create bean {fileName} {tableName}',
            'unit            php zls create unit {fileName}',
            '                ...',
        ];
    }

    public function execute($args)
    {
        try {
            call_user_func_array([z::factory(self::CREATE_CLASS_NAME), 'creation'], $this->getArgs($args));
        } catch (\Zls_Exception_Exit $e) {
            echo $e->getMessage();
        }
    }

    private function completion($type)
    {
        if (!in_array($type, self::TYPES)) {
            $abridge = [
                'c' => 'controller',
                'cmd' => 'command',
                'b' => 'business',
                'd' => 'dao',
                't' => 'task',
                'u' => 'unit',
            ];
            return Z::arrayGet($abridge, $type);

        }
        return $type;
    }

    private function getArgs($args, $type = '')
    {
        $name = Z::arrayGet($args, ['name', '-name', 3]);
        $type = $type ?: strtolower(z::arrayGet($args, ['type', '-type', 2]));

        if (!$type = $this->completion($type)) {
            $this->help($args);
            $this->error('unknown type, please use : --type [' . join(', ', self::TYPES) . ']', null, true);
        }

        if (!$name && strpos($args[1], ':')) {
            $name = Z::arrayGet($args, [2]);
        }
        if (empty($name)) {
            $this->error('filename cannot be empty , please use --name FileName', null, true);
        }
        $force = Z::arrayGet($args, ['force', 'f', '-force']);
        $style = Z::arrayGet($args, ['style', '-style']);
        $table = Z::arrayGet($args, ['table', '-table', 4]);
        $dbGroup = Z::arrayGet($args, ['db', '-db']);
        $hmvc = Z::arrayGet($args, ['hmvc', '-hmvc']);
        $argc = [$name, $type, $table, $hmvc, $dbGroup, $force, $style];
        if ($name[0] !== strtoupper($name[0])) {
            $name = Z::strSnake2Camel($name);
        }

        return ['name' => $name, 'type' => $type, 'table' => $table, 'hmvc' => $hmvc, 'dbGroup' => $dbGroup, 'force' => $force, 'style' => $style];
    }

    public function __call($name, $args)
    {
        call_user_func_array([z::factory(self::CREATE_CLASS_NAME), 'creation'], $this->getArgs($args[0], $name));
    }
}
