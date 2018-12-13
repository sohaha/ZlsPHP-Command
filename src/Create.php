<?php

namespace Zls\Command;

use z;

/**
 * Create.
 * @author        影浅-Seekwe
 * @email       seekwe@gmail.com
 * @updatetime    2017-2-27 16:52:51
 */
class Create extends Command
{
    const CREATE_CLASS_NAME = 'Zls\Command\Create\Common';
    private $args = [];

    public function description()
    {
        return 'Code Factory';
    }

    public function options()
    {
        return [
            '--type'  => 'Create type [controller,business,model,task,dao,bean]',
            '--db'    => 'Database Config Name',
            '--env'   => 'Environment',
            '--force' => 'Overwrite old files',
            '--hmvc'  => 'Hmvc Name',
        ];
    }

    public function example()
    {
        return [
            'controller      php zls create controller {controllerName}',
            'business        php zls create business {businessName}',
            'task            php zls create task {taskName}',
            'dao             php zls create dao {daoName} {tableName}',
            'dao and bean    php zls create dao:bean {fileName} {tableName}',
            '                ...',
        ];
    }

    public function execute($args)
    {
        call_user_func_array([z::factory(self::CREATE_CLASS_NAME), 'creation'], $this->getArgs($args));
    }

    private function getArgs($args, $type = '')
    {
        $name  = Z::arrayGet($args, ['name', '-name', 3]);
        $type  = $type ?: strtolower(z::arrayGet($args, ['type', '-type', 2]));
        $types = ['controller', 'business', 'model', 'task', 'dao', 'bean'];
        if (!in_array($type, $types)) {
            $this->error('type required, please use : --type [' . join(',', $types) . ']', null, true);
        }
        if (empty($name)) {
            $this->error('name required , please use --name FileName', null, true);
        }
        $force   = Z::arrayGet($args, ['force', 'f', '-force']);
        $style   = Z::arrayGet($args, ['style', '-style']);
        $table   = Z::arrayGet($args, ['table', '-table', 4]);
        $dbGroup = Z::arrayGet($args, ['db', '-db']);
        $hmvc    = Z::arrayGet($args, ['hmvc', '-hmvc']);
        $argc    = [$name, $type, $table, $hmvc, $dbGroup, $force, $style];

        return ['name' => $name, 'type' => $type, 'table' => $table, 'hmvc' => $hmvc, 'db' => $dbGroup, 'force' => $force, 'style' => $style];
    }

    public function __call($name, $args)
    {
        call_user_func_array([z::factory(self::CREATE_CLASS_NAME), 'creation'], $this->getArgs($args[0], $name));
    }
}
