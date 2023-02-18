<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;

class RoleModel extends \core\models\base\BaseModel
{

    use SingleTon;

    public function getRoles($data) {

        $sortArr = [
            'newest' => 'creation_date DESC',
            'oldest' => 'creation_date',
            'priority' => 'priority',
            'priority_desc' => 'priority DESC',
        ];

        $limit = !empty($data['limit']) ? $data['limit'] : 20;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $data['limit'] : 0;
        $order = !empty($data['sort']) && isset($sortArr[$data['sort']]) ? 'ORDER BY ' . $sortArr[$data['sort']] : '';
        $search = !empty($data['search']) ? $data['search'] : '';

        $where = '';

        if(!empty($search)) $where = "WHERE name LIKE '%$search%'";

        $query = "SELECT id, name, priority FROM roles $where $order LIMIT $page, $limit";

        return $this->query($query);

    }

    public function getRole($id) {

        $result = $this->query("SELECT * FROM roles WHERE id='$id'");

        if(isset($result[0])) return $result[0];

        return $result;

    }

    public function getPriorityRoles($arr_id) {

        $where = 'WHERE ';

        foreach ($arr_id as $id) $where .= "id='$id' OR ";

        $where = rtrim($where, 'OR ');

        return $this->query("SELECT id, name, priority FROM roles $where");

    }
}