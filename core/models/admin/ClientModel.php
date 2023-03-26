<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;

class ClientModel extends \core\models\base\BaseModel
{


    use SingleTon;

    public function getClients($data) {

        $sortArr = [
            'few_to_many_projects' => 'total_projects',
            'many_to_few_projects' => 'total_projects DESC',
            'newest' => 'id DESC',
            'oldest' => 'id'
        ];

        $limit = !empty($data['limit']) ? $data['limit'] : 20;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $limit : 0;
        $order = !empty($data['sort']) && isset($sortArr[$data['sort']]) ? 'ORDER BY ' . $sortArr[$data['sort']] : '';
        $search = !empty($data['search']) ? $data['search'] : '';

        $where = '';

        $join = 'LEFT JOIN projects as p ON p.client_id=c.id ';

        if(!empty($search)) $where = "WHERE c.name LIKE '%$search%' OR c.email LIKE '%$search%'";

        $query = "SELECT c.id, c.name, c.email, c.image, c.entity, COUNT(p.id) as total_projects FROM clients as c $join $where GROUP BY c.id $order LIMIT $page, $limit";

        return $this->query($query);

    }

    public function getClient($id) {

       $result = $this->query("SELECT * FROM clients WHERE id='$id'");

       if(isset($result[0])) return $result[0];

       return $result;

    }

}