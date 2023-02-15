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

        $where = '';

        $join = 'INNER JOIN roles as r ON r.id=a.role_id';

        if(!empty($search)) $where = "WHERE a.name LIKE '%$search%'";

        $query = "SELECT a.id, a.name, a.image, a.blocked, r.name as role FROM admins as a $join $where LIMIT $page, $limit";

        return $this->query($query);

    }

    public function getAdmin($id) {

        $join = 'INNER JOIN roles as r ON r.id=a.role_id';

        $result = $this->query("SELECT a.id, a.name, a.image, a.password, a.blocked, r.name as role FROM admins as a $join WHERE a.id='$id'");

        if(isset($result[0])) return $result[0];

        return $result;

    }
}