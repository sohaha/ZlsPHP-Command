<?php

namespace Zls\Command;

use z;

/**
 * Create
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
            '--name'  => 'FileName',
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
            'controller      php zls create controller --name {controllerName}',
            'business        php zls create business --name {businessName}',
            'task            php zls create task --name {taskName}',
            'dao             php zls create dao --name {Zls} --table {tableName}',
            'dao and bean    php zls create dao:bean -name {Zls} --table {tableName}',
            '                ...',
        ];
    }

    public function execute($args)
    {
        call_user_func_array([z::factory(self::CREATE_CLASS_NAME), 'creation'], $this->getArgs($args));
    }

    private function getArgs($args, $type = '')
    {
        $name = Z::arrayGet($args, ['name', '-name']);
        $type = $type ?: strtolower(z::arrayGet($args, ['type', '-type']));
        if(!$type){
            $type = z::arrayGet($args, 2);
        }
        if (empty($type)) {
            $this->error('type required, please use : --type [controller,business,model,task,dao,bean]');
            Z::finish();
        }
        if (empty($name)) {
            $this->error('name required , please use --name FileName');
            Z::finish();
        }
        $force = Z::arrayGet($args, ['force', 'f', '-force']);
        $style = Z::arrayGet($args, ['style', '-style']);
        $table = Z::arrayGet($args, ['table', '-table']);
        $dbGroup = Z::arrayGet($args, ['db', '-db']);
        $hmvc = Z::arrayGet($args, ['hmvc', '-hmvc']);
        $argc = [$name, $type, $table, $hmvc, $dbGroup, $force, $style];

        return ['name' => $name, 'type' => $type, 'table' => $table, 'hmvc' => $hmvc, 'db' => $dbGroup, 'force' => $force, 'style' => $style];
    }

    public function __call($name, $args)
    {
        call_user_func_array([z::factory(self::CREATE_CLASS_NAME), 'creation'], $this->getArgs($args[0], $name));
    }
}
