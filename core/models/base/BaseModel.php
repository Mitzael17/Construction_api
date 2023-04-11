<?php

namespace core\models\base;

use core\controllers\base\BaseMethods;
use core\controllers\base\SingleTon;
use core\exceptions\DbException;

class BaseModel
{

    use SingleTon;
    use BaseMethods;

    protected $db;

    protected function connect() {

        $this->db = new \mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if($this->db->connect_error) {
            throw new DbException('The database connection was failed', 500);
        }

    }

    public function query($query, $flag = 'r', $return_id = false) {

        $result = $this->db->query($query);

        if($this->db->affected_rows === -1) {
            throw new DbException("The query was failed: \r\n $query", 500);
        }

        switch ($flag) {

            case 'r':

                return $result->fetch_all(MYSQLI_ASSOC);

            case 'u':

                if($return_id) return $this->db->insert_id;

                return true;

            default:
                return true;

        }

    }

    protected function separateData($data) {

        $result = [];

        $index = 0;
        $stop_tables_array = [];

        foreach ($data as $arr) {

            foreach ($arr as $key => $value) {

                if(preg_match('/^TABLE_\w+_TABLE_/', $key)) {

                    [$table, $key] = explode('_TABLE_', $key);
                    $table = str_replace('TABLE_', '', $table);

                    if(array_search($table, $stop_tables_array) !== false) continue;

                    if(!isset($result[$table])) $result[$table] = [];

                    $last_id = count($result[$table]);

                    if($last_id > 0) $last_id--;

                    if($key === 'id' && isset($result[$table][$last_id])
                        && isset($result[$table][$last_id]['id']) && $result[$table][$last_id]['id'] === $value) {

                        $stop_tables_array[] = $table;

                        if(isset($result[$table][$index])) array_pop($result[$table]);

                        continue;

                    } elseif(
                        $key === 'id' && isset($result[$table][$last_id])
                        && isset($result[$table][$last_id - 1]['id']) && $result[$table][$last_id - 1]['id'] === $value
                    ) {
                        $stop_tables_array[] = $table;

                        if(isset($result[$table][$index])) array_pop($result[$table]);

                        continue;
                    }

                    if(!isset($result[$table][$index])) $result[$table][$index] = [];

                    $result[$table][$index][$key] = $value;

                    continue;

                }

                $result[$key] = $value;

            }

            $index++;

        }

        foreach ($result as $key => $value) {

            if(!is_array($value) || count($value) > 1) continue;

            $is_empty_array = true;

            foreach ($value[0] as $item) {

                if(!empty($item)) $is_empty_array = false;

            }

            if($is_empty_array) $result[$key] = [];

        }

        return $result;
    }

    protected function showTables() {

        $tables =  $this->query('SHOW TABLES');

        $key = '';

        foreach ($tables[0] as $database => $value) {

            $key = $database;

            break;

        }

        $result = [];

        foreach ($tables as $table) $result[] = $table[$key];

        return $result;

    }

    protected function showColumns($table) {

        return $this->query("SHOW COLUMNS FROM $table");

    }

    protected function createUpdate($data) {

        $update = 'SET ';

        foreach ($data as $key => $value) $update .= "`$key`='$value', ";

        $update = rtrim($update, ', ');

        return $update;

    }

    protected function createInsertValues($data) {

        $fields = '(';
        $values = 'VALUES(';

        foreach($data as $key => $value) {

            $fields .= "`$key`, ";
            $values .= "'$value', ";

        }

        $fields = rtrim($fields, ', ') . ') ';
        $values = rtrim($values, ', ') . ') ';

        return "$fields $values";

    }

    public function get($table, $id = '') {

        $where = '';

        if(!empty($id)) {

            $where .= 'WHERE ';

            if(is_array($id)) {

                foreach ($id as $item) {

                    $where .= "id='$item' OR ";

                }

                $where = rtrim($where, 'OR ');

            } else {
                $where .= "id='$id'";
            }

        }

        $result = $this->query("SELECT * FROM $table $where");

        if(!empty($result) && (!empty($id) && !is_array($id))) return $result[0];

        return $result;

    }

    public function update($table, $id, $data) {

        $update = $this->createUpdate($data);

        $this->query("UPDATE $table $update WHERE id=$id", 'u');

        return true;

    }

    public function create($table, $data) {

        $values = $this->createInsertValues($data);

        $query = "INSERT INTO $table $values";

        return $this->query($query, 'u', true);

    }

    public function delete(string $table, array $arr_id) {

        $where = array_reduce($arr_id, function($ac, $id) {

            return "$ac id='$id' OR ";

        }, 'WHERE ');

        $where = rtrim($where, ' OR ');

        $this->query("DELETE FROM $table $where", 'd');

        return true;

    }

    public function checkFkRelations(int $id, array $tables) {

        $result = [];

        foreach ($tables as $table_info) {

            $foreignKey = $table_info['foreign_key'];
            $table = $table_info['table'];

            $response = $this->query("SELECT * FROM $table WHERE `$foreignKey`='$id' LIMIT 1");

            if(!empty($response)) $result[$table] = true;

        }

        return $result;

    }

    public function checkUniqueField($table, $field, $value) {

        $result = $this->query("SELECT * FROM $table WHERE `$field`='$value' LIMIT 1");

        if(empty($result)) return false;

        return true;

    }

    public function getNames($table, $arr_id) {

        $where = 'WHERE ';

        foreach ($arr_id as $id) {

            $where .= "id='$id' OR ";

        }

        $where = rtrim($where, 'OR ');

        return $this->query("SELECT name FROM $table $where");

    }

    public function getByData(string $table, array $data, int $limit = null) {

        $where = 'WHERE ';

        foreach ($data as $key => $value) {

            $where .= "$key='$value' AND ";

        }

        $limit = '';

        if($limit) {

            $limit = "LIMIT $limit";

        }

        $where = rtrim($where, 'AND ');

        return $this->query("SELECT * FROM $table $where $limit");

    }

}