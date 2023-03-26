<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;
use core\exceptions\DbException;

class SearchModel extends \core\models\base\BaseModel
{

    use SingleTon;

    public function search($table, $field, $value, $limit = 5) {

        $tables = $this->showTables();

        if(array_search($table, $tables) === false) throw new DbException('The table doesn\'t exist', 404);

        return $this->query("SELECT id, $field as 'name' FROM $table WHERE $field LIKE '$value%'
                                    UNION 
                                    SELECT id, $field FROM $table WHERE $field LIKE '%$value%' LIMIT $limit");

    }

}