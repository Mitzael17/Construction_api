<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;

class AdminModel extends \core\models\base\BaseModel
{

    use SingleTon;

    public function getAdmins($data) {

        $limit = !empty($data['limit']) ? $data['limit'] : 20;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $data['limit'] : 0;
        $search = !empty($data['search']) ? $data['search'] : '';
        $priority = $data['priority'];

        $where = "WHERE r.priority > $priority";

        $join = 'INNER JOIN roles as r ON r.id=a.role_id';

        if(!empty($search)) $where = " AND a.name LIKE '%$search%'";

        $query = "SELECT a.id, a.name, a.image, a.blocked, r.name as role FROM admins as a $join $where LIMIT $page, $limit";

        return $this->query($query);

    }

    public function getAdmin($id) {

        $join = 'INNER JOIN roles as r ON r.id=a.role_id';

        $result = $this->query("SELECT a.id, a.name, a.image, a.password, a.blocked, r.name as role, r.priority FROM admins as a $join WHERE a.id='$id'");

        if(isset($result[0])) return $result[0];

        return $result;

    }

    public function getPriorityAdmins($arr_id) {

        $where = 'WHERE ';

        foreach ($arr_id as $id) $where .= "a.id='$id' OR ";

        $where = rtrim($where, 'OR ');

        return $this->query("SELECT a.name, r.priority FROM admins as a INNER JOIN roles as r ON r.id=a.role_id $where");

    }
}