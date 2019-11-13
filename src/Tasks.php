<?php

namespace Zls\Command;

use Z;
use Zls\Action\ApiDoc;

class Tasks extends Command
{
    public function description()
    {
        return 'Display current task list';
    }

    public function options()
    {
        return [];
    }

    public function commands()
    {
        return [];
    }

    public function example()
    {
        return [];
    }

    public function execute($args)
    {
        $this->list($args);
    }

    public function list($args)
    {
        $tasks    = [];
        $cfg      = Z::config();
        $taksPath = Z::realPath(ZLS_APP_PATH . $cfg->getClassesDirName() . '/' . $cfg->getTaskDirName(), true);
        ApiDoc::listDirApiPhp($taksPath, $tasks, null, 'Task.php', 'Task');
        $ret = [];
        foreach ($tasks as $class) {
            try {
                $data                                                                    = ApiDoc::docComment($class['controller'], $class['hmvc'], false, $class['time']);
                $title                                                                   = Z::arrayGet($data, '0.class.title');
                $ret[' -task ' . str_replace('_', '/', substr($class['controller'], 5))] = $title !== '{请检查函数注释}' ? $title : '';
            } catch (\ReflectionException $e) {
            }
        }
        $this->printStrN('Current task list:', 'yellow');
        $handles       = $this->beautify($ret, '', 'cyan', ':');
        $commandPrefix = z::arrayGet($args, 0) . ' ';
        foreach ($handles as $k => $v) {
            $this->printStrN(' ' . $commandPrefix . ltrim($v));
        }
    }
}
