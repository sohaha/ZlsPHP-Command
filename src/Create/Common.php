<?php

namespace Zls\Command\Create;

use Z;
use Zls\Command\Utils;

/**
 * Zls_Command_Create_Common.
 * @author        影浅 seekwe@gmail.com
 */
class Common
{
    use Utils;
    const CREATE_MYSQL_CLASS_NAME = 'Zls\Command\Create\Mysql';
    private $hmvc;

    public function creation($name, $type, $table, $hmvc, $dbGroup, $force, $style = 'PSR4')
    {
        $afresh = false;
        if (!in_array($style, ['PSR4', 'PSR0'])) {
            $style = 'PSR4';
        }
        $this->hmvc = $hmvc;
        /** @var \Zls_Config $config */
        $config         = Z::config();
        $classesDir     = $config->getPrimaryAppDir() . $config->getClassesDirName() . '/';
        $getHmvcModules = $config->getHmvcModules();
        if ($this->hmvc && $HmvcModules = Z::arrayGet($getHmvcModules, $this->hmvc)) {
            $classesDir = $config->getPrimaryAppDir() . $config->getHmvcDirName() . '/' . $HmvcModules . '/' . $config->getClassesDirName() . '/';
        }
        switch ($type) {
            case 'controller':
                // list($name) = $this->nameVerify($name, false, $type);
                $info = [
                    'dir'         => $config->getControllerDirName(),
                    'parentClass' => 'Zls_Controller',
                    'method'      => 'public function ' . Z::config()->getMethodPrefix() . 'index()' . "\n    {\n\n    }",
                    'nameTip'     => 'Controller',
                ];
                break;
            case 'business':
                list($name) = $this->nameVerify($name, false, $type);
                $info = [
                    'dir'         => $config->getBusinessDirName(),
                    'parentClass' => 'Zls_Business',
                    'method'      => "public function business()\n    {\n\n    }",
                    'nameTip'     => 'Business',
                ];
                break;
            case 'model':
                list($name) = $this->nameVerify($name, false, $type);
                $info = [
                    'dir'         => $config->getModelDirName(),
                    'parentClass' => 'Zls_Model',
                    'method'      => "public function model()\n    {\n\n    }",
                    'nameTip'     => 'Model',
                ];
                break;
            case 'task':
                list($name) = $this->nameVerify($name, false, $type);
                $info = [
                    'dir'         => $config->getTaskDirName(),
                    'parentClass' => 'Zls_Task',
                    'method'      => "public function execute(\$args)\n    {\n\n    }",
                    'nameTip'     => 'Task',
                ];
                break;
            case 'dao':
                list($name, $table) = $this->nameVerify($name, $table, $type);
                $afresh = true;
                list($method, $warn) = z::factory(self::CREATE_MYSQL_CLASS_NAME, true)->creation($type, $table, $dbGroup);
                if ($warn) {
                    $this->warning($warn);
                }
                $info = [
                    'dir'         => $config->getDaoDirName(),
                    'parentClass' => 'Zls_Dao',
                    'method'      => $method,
                    'nameTip'     => 'Dao',
                ];
                break;
            case 'bean':
                //$afresh = true;
                list($name, $table) = $this->nameVerify($name, $table, $type);
                $info = [
                    'dir'         => $config->getBeanDirName(),
                    'parentClass' => 'Zls_Bean',
                    'method'      => z::factory(self::CREATE_MYSQL_CLASS_NAME, true)->creation($type, $table, $dbGroup),
                    'nameTip'     => 'Bean',
                ];
                break;
            default:
                Z::finish("Unknown type : {$type}\n Please use : -type [controller,business,model,task,dao,bean]");
        }
        $classname   = $name;
        $typename    = $info['dir'];
        $file        = $classesDir . str_replace('_', '/', $typename . '_' . $classname) . '.php';
        $file        = Z::realPath($file);
        $method      = $info['method'];
        $parentClass = $info['parentClass'];
        $tip         = $info['nameTip'];
        if (file_exists($file)) {
            if ($afresh) {
                $this->afreshFile($file, $typename, $classname, $tip, $type, $table, $dbGroup);
            } elseif ($force) {
                $this->writeFile($typename, $classname, $method, $parentClass, $file, $tip, $style);
            } else {
                $tip .= " [ {$classname} ] already exists. you can use -force to force the file.";
                echo self::error($tip);
            }
        } else {
            $this->writeFile($typename, $classname, $method, $parentClass, $file, $tip, $style);
        }
    }

    private  function nameVerify($name, $table, $suffix)
    {
        $getTable = function ($name, $table) {
            if (is_null($table)) {
                $table = explode('/', $name);
                $table = Z::strCamel2Snake(end($table));
            }
            return $table;
        };
        $suffix = ucfirst($suffix);
        if (!Z::strEndsWith($name, $suffix)) {
            $table = $getTable($name, $table);
            $name = $name .  $suffix;
        } else {
            $table = $getTable(substr($name, 0, strlen($name) - strlen($suffix)), $table);
        }
        return [$name, $table];
    }
    /**
     * @param $file
     * @param $typename
     * @param $classname
     * @param $type
     * @param $table
     * @param $dbGroup
     */
    private function afreshFile($file, $typename, $classname, $tip, $type, $table, $dbGroup)
    {
        $content  = '';
        $typename = ($this->hmvc ? 'Hmvc_' . $typename : '' . $typename) . '_';
        z::includeOnce($file);
        $obj = z::factory($typename . $classname);

        try {
            $ref     = new \ReflectionClass($obj);
            $factory = z::factory(self::CREATE_MYSQL_CLASS_NAME, true)->afresh();
            if ($factory) {
                $daoMethods = [];
                $content    = file($file);
                $place      = 0;
                $methods    = $factory['methods'];
                foreach ($factory['methods'] as $v) {
                    try {
                        if ($method = $ref->getMethod($v)) {
                            if (Z::realPath($file) === Z::realPath($method->getFileName())) {
                                $start = $method->getStartLine() - 1;
                                $end   = $method->getEndLine() - 1;
                                do {
                                    if (!$place) {
                                        $place           = true;
                                        $content[$start] = $factory['code'];
                                    } else {
                                        unset($content[$start]);
                                    }
                                    ++$start;
                                } while ($start <= $end);
                            }
                        }
                    } catch (\ReflectionException $e) {
                        $this->error($e->getMessage());
                    }
                }
                if (!$place) {
                    $endLine  = $ref->getEndLine() - 1;
                    $_content = trim(z::arrayGet($content, $endLine));
                    if (!$_content) {
                        $endLine  = $endLine - 1;
                        $_content = trim(z::arrayGet($content, $endLine));
                    }
                    $content[$endLine] = $factory['code'] . \PHP_EOL . $_content;
                }
                $content = implode($content);
            }
            if ($content && file_put_contents($file, $content)) {
                $this->success("{$tip} [ {$classname} ] created successfully.");
                $this->printStrN("FilePath: {$file}");
                $this->printStrN();
            }
        } catch (\ReflectionException $e) {
            echo $e->getMessage();
        }
    }

    private function writeFile($typename, $classname, $method, $parentClass, $file, $tip, $style)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $content = $this->$style($typename, $classname, $parentClass, $method);
        if (file_put_contents($file, $content)) {
            $this->success("{$tip} [ {$classname} ] created successfully");
            $this->printStrN("FilePath: {$file}");
        }
    }

    private function PSR0($typename, $classname, $parentClass, $method)
    {
        $classname = $this->hmvc ? 'Hmvc_' : '';

        return vsprintf("<?php\n\nclass {$classname}%s_%s extends %s \n{\n    %s\n}", [$typename, $classname, $parentClass, $method]);
    }

    private function PSR4($typename, $classname, $parentClass, $method)
    {
        $classname    = str_replace('\\', '/', $classname);
        $classname    = str_replace('_', '/', $classname);
        $classnameArg = explode('/', $classname);
        $classname    = array_pop($classnameArg);
        $classnameArg = implode('\\', $classnameArg);
        if ($classnameArg) {
            $typename = $typename . '\\' . $classnameArg;
        }
        $namespace = $this->hmvc ? 'namespace Hmvc\\' : 'namespace ';

        return vsprintf("<?php\n\n{$namespace}%s;\nuse Z;\n\nclass %s extends \%s \n{\n    %s\n}", [$typename, $classname, $parentClass, $method]);
    }
}
