<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;

class LogModel extends \core\models\base\BaseModel
{

    use SingleTon;

    public function getLogs($data) {

        $limit = !empty($data['limit']) ? $data['limit'] : 20;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $limit : 0;
        $search = !empty($data['search']) ? $data['search'] : '';
        $from = !empty($data['date_from']) ? $data['date_from'] : '';
        $to = !empty($data['date_to']) ? $data['date_to'] : '';

        $where_value = '';

        if(!empty($from)) $where_value .= "l.date_and_time>='$from' AND ";
        if(!empty($to)) $where_value .= "l.date_and_time<='$to' AND ";

        $join = 'INNER JOIN admins as a ON a.id=l.admin_id INNER JOIN roles as r ON r.id=a.role_id';

        if(!empty($search)) $where_value .= "a.name LIKE '%$search%'";

        $where = '';

        if(!empty($where_value)) $where = 'WHERE ' . rtrim($where_value, 'AND ');

        $fields = "a.name as TABLE_admin_TABLE_name, a.image as TABLE_admin_TABLE_image, r.name as TABLE_admin_TABLE_role, l.message, l.date_and_time, l.id";


        $response = $this->query("SELECT $fields FROM logs as l $join $where ORDER BY date_and_time DESC LIMIT $page, $limit");

        $result = [];

        foreach ($response as $value) {
            $value = $this->separateData([$value]);
            $value['admin'] = $value['admin'][0];
            $result[] = $value;
        }

        return $result;

    }

}