<?php

namespace core\models\base;

use core\exceptions\DbException;

abstract class BaseModel
{

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

        return $result;
    }

}