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


        $query = "SELECT p.id, p.name, p.creation_date, p.end_date, s.name as service, c.name as client, ps.name as status FROM projects as p $join $where $order LIMIT $page, $limit";

        return $this->query($query);

    }

    public function getProject($id) {

        $query = "SELECT c.id as TABLE_client_TABLE_id, c.name as TABLE_client_TABLE_name, c.email as TABLE_client_TABLE_email, p.creation_date, 
                  p.end_date, p.name, s.name as TABLE_service_TABLE_name, s.id as TABLE_service_TABLE_id,
                  ppc.alias, com.date as TABLE_comments_TABLE_date, com.id as TABLE_comments_TABLE_id,com.text as TABLE_comments_TABLE_text 
                  FROM projects as p 
                  INNER JOIN clients as c ON c.id=p.client_id 
                  INNER JOIN services as s ON s.id=p.service_id 
                  LEFT JOIN project_page_content as ppc ON ppc.id=project_page_content_id
                  LEFT JOIN comments as com ON com.project_id=p.id
                  WHERE p.id=$id";

        $response = $this->query($query);

        $result = [];

        if(!empty($response)) $result = $this->separateData($response);

        return $result;

    }
}