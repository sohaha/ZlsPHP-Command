<?php

namespace Zls\Command;

use Z;

/**
 * Command_Utils.
 * @author        影浅-seekwe@gmail.com
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
            $this->colors   = [
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
            ? false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI')
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

    final public function warning($err, $color = 'dark_gray')
    {
        $this->printStr('[ warning ]', 'yellow');
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
        return ($this->showColor && isset($colors[$color])) ? "\033[" . $colors[$color] . 'm' : '';
    }

    final public function printStrN($str = '', $color = '', $bgColor = null)
    {
        echo $this->color($str, $color, $bgColor);
        echo PHP_EOL;
    }

    final public function error($err, $color = '', $end = false)
    {
        if (!$color) {
            $color = 'red';
        }
        $this->printStr('[ Error ]', 'white', 'red');
        $this->printStrN(': ' . $err, $color);
        $end && Z::end();
    }

    final public function success($msg, $color = 'green')
    {
        $this->printStr('[ Success ]', 'white', 'green');
        $this->printStrN(': ' . $msg, $color);
    }

    final public function input($question, $default = null, $canNull = false, callable $verification = null)
    {
        $question = $this->color('[ Inpit ]', 'light_cyan') . ': ' . $question;

        return $this->ask($question, $default, $canNull, $verification);
    }

    final public function ask($question, $default = null, $canNull = false, callable $verification = null)
    {
        $status = false;
        do {
            fwrite(STDOUT, $question);
            $value = trim(fgets(STDIN));
            if (!is_null($default) && ('' === $value || '0' === $value)) {
                $value  = $default;
                $status = true;
            } elseif ($value || !$canNull) {
                $status = true;
            } elseif (is_string($canNull)) {
                $question = $canNull;
            }
            if ($status && $verification) {
                $status = $verification($value);
            }
        } while (!$status);

        return $value;
    }

    final public function progress($i, $title = 'mprogress: ', $mprogressColor = '', $bgColor = '', $pad = ' ')
    {
        $bgColor        = $bgColor ? "\033[" . z::arrayGet($this->bgColors, $bgColor, 'white') . 'm' : '';
        $mprogressColor = $mprogressColor ? "\033[" . z::arrayGet($this->colors, $mprogressColor, 'white') . 'm' : '';
        printf("{$title}{$bgColor}{$mprogressColor} %d%% %s\r\033[0m", $i, str_repeat($pad, $i));
    }

    final public function copyFile($originFile, $file, $force = false, \Closure $cb = null, $tip = 'copy config: ')
    {
        $originFile = Z::realPath($originFile, false, false);
        $file       = Z::realPath($file, false, false);
        $status     = false;
        if (!file_exists($file) || $force) {
            if ($tip) {
                $this->printStrN("{$tip}{$originFile} -> {$file}");
            }
            $status = (bool)@copy($originFile, $file);
        }
        if (is_callable($cb)) {
            $cb($status);
        }
    }

    /**
     * 目录复制
     *
     * @param string  $originDatabasePath
     * @param string  $databasePath
     * @param boolean $allForce 是否全部覆盖
     * @param null    $destPathProcess
     */
    final public function batchCopy($originDatabasePath, $databasePath, $allForce = false, $destPathProcess = null)
    {
        $originDatabasePath = Z::realPath($originDatabasePath);
        $databasePath       = Z::realPath($databasePath);
        $this->listDir($originDatabasePath, $arr);
        $copy = function ($file, $destFinalPath, $dest) {
            if (!@copy($file, $destFinalPath)) {
                $this->error('Copy error -> ' . Z::safePath($destFinalPath));
            } else {
                @touch($destFinalPath, filemtime($file));
                $this->success('Copy success -> ' . Z::safePath($destFinalPath));
            }
        };
        foreach ($arr as $file) {
            $destPath = str_replace($originDatabasePath, $databasePath, $file);
            if (is_callable($destPathProcess)) {
                $destFinalPath = $destPathProcess($destPath, $file);
            } else {
                $destFinalPath = $destPath;
            }
            if (file_exists($destFinalPath)) {
                if (filemtime($destFinalPath) != filemtime($file)) {
                    if (@file_get_contents($destFinalPath) === @file_get_contents($file)) {
                        continue;
                    } elseif ($allForce) {
                        $copy($file, $destFinalPath, $destPath);
                    } else {
                        $pad    = str_repeat(' ', 12);
                        $notice = $this->color('[ Notify ]', 'white', 'blue');
                        $msg    = $notice . ': File exists ' . Z::safePath($destFinalPath) . "\n{$pad}" . $this->color('Whether to overwrite the current file [y,N] ', 'cyan');
                        $value  = $this->ask($msg, 'n');
                        $value  = strtoupper(trim($value));
                        if ($value === 'Y' || $value === 'YES') {
                            $copy($file, $destFinalPath, $destPath);
                        } else {
                            $this->printStr($this->color('[ Skip ]', 'white', 'cyan'));
                            $this->printStrN(': Skip replacement', 'light_cyan');
                        }
                    }
                }
            } else {
                $destFinalPath = z::realPathMkdir($destFinalPath, false, true);
                $copy($file, $destFinalPath, $destPath);
            }
        }
    }

    /**
     * 读取文件列表
     *
     * @param $dir
     * @param $arr
     */
    private function listDir($dir, &$arr)
    {
        if (is_dir($dir) && ($dh = opendir($dir))) {
            while (false !== ($file = readdir($dh))) {
                if ('.' === $file || '..' === $file) {
                    continue;
                }
                $filePath = Z::realPath($dir . '/' . $file);
                if ((is_dir($filePath))) {
                    $this->listDir($dir . '/' . $file, $arr);
                } else {
                    $arr[] = $filePath;
                }
            }
            closedir($dh);
        }
    }
}
