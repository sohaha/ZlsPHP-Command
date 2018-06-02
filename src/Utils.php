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

    final public function getColors()
    {
        return $this->colors;
    }

    final public function getBgColors()
    {
        return $this->bgColors;
    }

    final public function error($err, $color = 'red')
    {
        $this->echo('[ Error ]', 'white', 'red');
        $this->echoN(': ' . $err, $color);
    }

    final public function echo($str = '', $color = '', $bgColor = null)
    {
        echo $this->color($str, $color, $bgColor);
    }

    final public function color($str = '', $color = null, $bgColor = null)
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

    final public function echoN($str = '', $color = '', $bgColor = null)
    {
        echo $this->color($str, $color, $bgColor);
        echo PHP_EOL;
    }

    final public function success($msg, $color = 'green')
    {
        $this->echo('Success', 'white', 'green') . $this->echoN(': ' . $msg, $color);
    }

    final public function ask($question, $default = null, $canNull = false)
    {
        $status = false;
        do {
            fwrite(STDOUT, $question);
            $value = trim(fgets(STDIN));
            if ($value || !$canNull) {
                $status = true;
            } elseif (is_string($canNull)) {
                $question = $canNull;
            }
        } while (!$status);

        return $value;
    }

    final public function progress($i, $title = 'mprogress: ', $mprogressColor = '', $bgColor = '', $pad = ' ')
    {
        $bgColor = $bgColor ? "\033[" . z::arrayGet($this->bgColors, $bgColor, 'white') . "m" : '';
        $mprogressColor = $mprogressColor ? "\033[" . z::arrayGet($this->colors, $mprogressColor, 'white') . "m" : '';
        printf("{$title}{$bgColor}{$mprogressColor} %d%% %s\r\033[0m", $i, str_repeat($pad, $i));
    }
}
