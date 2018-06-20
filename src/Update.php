<?php

namespace Zls\Command;

use Z;

/**
 * 更新
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @link          ---
 * @since         v0.0.1
 * @updatetime    2018-02-01 15:01
 */
class Update extends Command
{
    const LOG_URL = 'http://localhost:5000/update.json';
    const FRAMEWORK_URL = 'http://localhost:5000/application/core/Zls.php';

    public function description()
    {
        return 'Update ZlsPHP Framework';
    }

    public function options()
    {
        return [];
    }

    public function handle()
    {
        return [];
    }

    public function example()
    {
        return [];
    }


    public function execute($args)
    {
        //获取当前版本与远程对比
        $version = IN_ZLS;
        /**
         * @var \Zls\Action\Http $Action \Http
         */
        $ActionHttp = z::extension('Action\Http');
        $updateLog = $ActionHttp->get(self::LOG_URL);
        $updateLog = @json_decode($updateLog, true);
        $updateLog = z::arrayFilter($updateLog, function ($e, $k) use ($version) {
            return $k > $version;
        });
        if($updateLog){
            $log = ['Update Log' . PHP_EOL];
            foreach ($updateLog as $v => $t) {
                $log[] = $v . ":\n" . $t . PHP_EOL;
            }
            $this->printStrN(join("\n", $log), 'green');
        }else{
            $this->printStrN(join("\n", $log), 'green');
        }

    }

    public function download()
    {
        //FRAMEWORK_URL
        /**
         * @var \Zls\Action\Http $Action \Http
         */
        $ActionHttp = z::extension('Action\Http');
        $content = $ActionHttp->get(self::FRAMEWORK_URL);
        z::dump($content);
        z::finish();
    }
}
