<?php

namespace Zls\Command\Create;

use Z;
use Zls\Command\Utils;

class Mysql
{
    use Utils;

    private $type;
    private $table;
    private $dbGroup;

    public function creation($type, $table, $dbGroup)
    {
        if (empty($table) || !is_string($table)) {
            $this->error('table name required, please use --table TableName');
            exit(5);
        } else {
            $this->type = $type;
            $this->table = $table;
            $this->dbGroup = $dbGroup;
            $columns = $this->getTableFieldsInfo($table, $dbGroup);

            return $this->$type($columns, $table);
        }
    }

    public function getTableFieldsInfo($tableName, $db)
    {
        if (!is_object($db)) {
            $db = Z::db($db);
        }
        $type = strtolower($db->getDriverType());
        $info = [];
        /*try {*/
        if (method_exists($this, $type)) {
            $info = $this->$type($tableName, $db);
        }

        /*} catch (\Exception $e) {$this->error($e->getMessage());Z::finish();}*/

        return $info;
    }

    /**
     * @param string $tableName
     * @param \Zls_Database_ActiveRecord $db
     * @return array
     */
    public function sqlsrv($tableName, $db)
    {
        $info = [];
        $result = $db->execute('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=\'' . $db->getTablePrefix() . $tableName . '\'')->rows();
        $primary = $db->execute('EXEC sp_pkeys @table_name=\'' . $db->getTablePrefix() . $tableName . '\'')->value('COLUMN_NAME');
        if ($result) {
            foreach ($result as $val) {
                $info[$val['COLUMN_NAME']] = [
                    'name' => $val['COLUMN_NAME'],
                    'type' => $val['DATA_TYPE'],
                    'comment' => $val['COLUMN_NAME'], //注释
                    'notnull' => 'NO' == $val['IS_NULLABLE'] ? 1 : 0,
                    'default' => $val['COLUMN_DEFAULT'],
                    'primary' => (strtolower($val['COLUMN_NAME']) === strtolower($primary)),
                    'autoinc' => (strtolower($val['COLUMN_NAME']) === strtolower($primary)),
                ];
            }
        }

        return $info;
    }

    public function afresh($being = false)
    {
        $type = $this->type;
        $columns = $this->getTableFieldsInfo($this->table, $this->dbGroup);
        $result['methods'] = [];
        $result['args'] = [];
        switch ($type) {
            case 'dao':
                list($code, $warn, $methods) = $this->$type($columns, $this->table, $being);
                $result['code'] = '    ' . $code;
                $result['methods'] = $methods;
                break;
            default:
                $result['code'] = '    ' . $this->$type($columns, $this->table, $being) . PHP_EOL;
                foreach ($columns as $column) {
                    $result['methods'][] = 'get' . Z::strSnake2Camel($column['name']);
                    $result['methods'][] = 'set' . Z::strSnake2Camel($column['name']);
                }
        }

        return $result;
    }

    /**
     * @param string $tableName
     * @param \Zls_Database_ActiveRecord $db
     * @return array
     */
    private function mysql($tableName, $db)
    {
        $info = [];
        $result = $db->execute('SHOW FULL COLUMNS FROM ' . $db->getTablePrefix() . $tableName)->rows();
        if ($result) {
            foreach ($result as $val) {
                $info[$val['Field']] = [
                    'name' => $val['Field'],
                    'type' => $val['Type'],
                    'comment' => $val['Comment'] ? $val['Comment'] : $val['Field'],
                    'notnull' => 'NO' == $val['Null'] ? 1 : 0,
                    'default' => $val['Default'],
                    'primary' => ('pri' == strtolower($val['Key'])),
                    'autoinc' => ('auto_increment' == strtolower($val['Extra'])),
                ];
            }
        }

        return $info;
    }

    private function dao($columns, $table, $isAfresh = false)
    {
        /** @var \Zls\Dao\Create $DaoCreate */
        $DaoCreate = Z::extension('Dao\Create');

        return $DaoCreate->dao($columns, $table, $isAfresh);
    }

    private function bean($columns)
    {
        /**
         * @var \Zls\Dao\Create
         */
        $DaoCreate = Z::extension('Dao\Create');
        return $DaoCreate->bean($columns);
    }
}
