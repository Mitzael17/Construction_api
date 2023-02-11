<?php

namespace core\models\admin;

use core\models\base\BaseModel;
use core\controllers\base\SingleTon;

class ProjectModel extends BaseModel
{

    use SingleTon;

    public function getProjects($data) {

        $sortArr = [
            'newest' => 'creation_date DESC',
            'oldest' => 'creation_date',
        ];


        $limit = !empty($data['limit']) ? $data['limit'] : 20;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $data['limit'] : 0;
        $order = !empty($data['sort']) && isset($sortArr[$data['sort']]) ? 'ORDER BY ' . $sortArr[$data['sort']] : '';
        $search = !empty($data['search']) ? $data['search'] : '';
        $status_id = !empty($data['status_id']) ? $data['status_id'] : '';

        $where = '';
        $join = 'INNER JOIN project_status as ps ON ps.id=p.project_status_id ';
        $join .=  'INNER JOIN services as s ON s.id=p.service_id ';
        $join .= 'INNER JOIN clients as c ON c.id=p.client_id';

        if(!empty($status_id) || !empty($search)) {

            $where = 'WHERE ';

            if(!empty($status_id)) {
                $where .= "project_status_id=$status_id AND ";
            }
            if(!empty($search)) $where .= "p.name LIKE '%$search%'";

            $where =  rtrim($where, ' AND ');

        }


        $query = "SELECT p.id, p.name, p.creation_date, s.name as service, c.name as client, ps.name as status FROM projects as p $join $where $order LIMIT $page, $limit";

        return $this->query($query);

    }

}