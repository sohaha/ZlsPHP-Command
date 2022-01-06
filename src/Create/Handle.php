<?php

namespace Zls\Command\Create;

use Z;

class Handle
{
    public function command()
    {
        $method = '
    /**
     * 命令执行
     * @param $args
     */
    public function execute($args)
    {

    }

    /**
     * 命令配置
     * @return array
     */
    public function options()
    {
      return [];
    }

    /**
     * 命令介绍
     * @return string
     */
    public function description(){
      return "命令名称";
    }

    /**
     * 命令示例
     * @return array
     */
    public function example()
    {
        return [];
    }
        ';
        return [
            'dir' => 'Command',
            'parentClass' => 'Zls\Command\Command',
            'method' => $method,
            'nameTip' => 'Command',
            'suffix' => true,
        ];
    }

    public function controller()
    {
        // list($name) = $this->nameVerify($name, false, $type);
        return [
            'dir' => Z::config()->getControllerDirName(),
            'parentClass' => 'Zls_Controller',
            'method' => 'public function ' . Z::config()->getMethodPrefix() . 'index()' . "\n    {\n\n    }",
            'nameTip' => 'Controller',
        ];
    }
}
