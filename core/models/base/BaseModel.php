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

}