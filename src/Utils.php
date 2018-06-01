<?php

namespace Zls\Command;

/**
 * Utils
 * @author        影浅-Seekwe
 * @email         seekwe@gmail.com
 * @updatetime    2018-5-31 16:48:37
 */
trait Utils
{
    private $colors = [
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    ];
    private $bgColors = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    ];

    public final function getColors()
    {
        return $this->colors;
    }

    public final function getBgColors()
    {
        return $this->bgColors;
    }

    public final function error($err, $color = 'red')
    {
        $this->str('[ Error ]', 'white', 'red');
        $this->strN(': ' . $err, $color);
    }

    public final function str($str = '', $color = '', $bgColor = null)
    {
        echo $this->color($str, $color, $bgColor);
    }

    public final function color($str = '', $color = null, $bgColor = null)
    {
        $colorStr = "";
        if (isset($this->colors[$color])) {
            $colorStr .= "\033[" . $this->colors[$color] . "m";
        }
        if (isset($this->bgColors[$bgColor])) {
            $colorStr .= "\033[" . $this->bgColors[$bgColor] . "m";
        }
        $colorStr .= $str . "\033[0m";

        return $colorStr;
    }

    public final function strN($str = '', $color = '', $bgColor = null)
    {
        echo $this->color($str, $color, $bgColor);
        echo PHP_EOL;
    }

    public final function success($msg, $color = 'green')
    {
        $this->str('Success', 'white', 'green') . $this->strN(': ' . $msg, $color);
    }

    public final function ask($question, $default = null, $canNull = false)
    {
        $status = false;
        do {
            fwrite(STDOUT, $question);
            $value = trim(fgets(STDIN));
            if ($value || $canNull) {
                $status = true;
            } elseif (is_string($canNull)) {
                $question = $canNull;
            }
        } while (!$status);
        //system('clear');
        //z::command('clear');
        return $value;
    }

}
