<?php

namespace Zls\Command;

use Z;

/**
 * Command\Utils
 * @author        影浅-Seekwe
 * @email         seekwe@gmail.com
 * @updatetime    2018-7-13 11:54:51
 */
trait Utils
{
    private $showColor;
    private $colors = [];
    private $bgColors = [];

    public function __construct()
    {
        $this->initColor();
    }

    public function initColor()
    {
        $this->showColor = $this->ansiColorsSupported();
        if ($this->showColor) {
            $this->colors = [
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
            $this->bgColors = [
                'black'      => '40',
                'red'        => '41',
                'green'      => '42',
                'yellow'     => '43',
                'blue'       => '44',
                'magenta'    => '45',
                'cyan'       => '46',
                'light_gray' => '47',
            ];
        }
    }

    private function ansiColorsSupported()
    {
        return DIRECTORY_SEPARATOR === '\\'
            ? getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON'
            : function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }

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
        $this->printStr('[ Error ]', 'white', 'red');
        $this->printStrN(': ' . $err, $color);
    }

    final public function printStr($str = '', $color = '', $bgColor = null)
    {
        echo $this->color($str, $color, $bgColor);
    }

    final public function color($str = '', $color = null, $bgColor = null)
    {
        $colorStr = '';
        $colorStr .= $this->_color($color, $this->colors);
        $colorStr .= $this->_color($bgColor, $this->bgColors);
        $colorStr .= $str . $this->_color(0, [0]);

        return $colorStr;
    }

    final public function _color($color = '', array $colors = [])
    {
        return ($this->showColor && isset($colors[$color])) ? "\033[" . $colors[$color] . "m" : '';
    }

    final public function printStrN($str = '', $color = '', $bgColor = null)
    {
        echo $this->color($str, $color, $bgColor);
        echo PHP_EOL;
    }

    final public function success($msg, $color = 'green')
    {
        $this->printStr('[ Success ]', 'white', 'green');
        $this->printStrN(': ' . $msg, $color);
    }

    final public function ask($question, $default = null, $canNull = false)
    {
        $status = false;
        do {
            fwrite(STDOUT, $question);
            $value = trim(fgets(STDIN));
            if (!is_null($default) && ($value === '' || $value === '0')) {
                $value = $default;
                $status = true;
            } elseif ($value || !$canNull) {
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

    final public function copyFile($originFile, $file, $force = false, \Closure $cb = null, $tip = 'copy config: ')
    {
        $originFile = Z::realPath($originFile, false, false);
        $file = Z::realPath($file, false, false);
        $status = false;
        if (!file_exists($file) || $force) {
            if ($tip) {
                $this->printStrN("{$tip}{$originFile} -> {$file}");
            }
            $status = !!@copy($originFile, $file);
        }
        if ($cb instanceof \Closure) {
            $cb($status);
        }
    }
}
