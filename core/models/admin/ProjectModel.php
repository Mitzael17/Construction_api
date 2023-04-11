<?php

namespace core\models\admin;

use core\exceptions\RouteException;
use core\models\base\BaseModel;
use core\controllers\base\SingleTon;

class ProjectModel extends BaseModel
{

    use SingleTon;

    public function getProjects($data) {

        $sortArr = [
            'newest' => 'id DESC',
            'oldest' => 'id ASC',
        ];


        $limit = !empty($data['limit']) ? $data['limit'] : 50;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $limit : 0;
        $order = !empty($data['sort']) && isset($sortArr[$data['sort']]) ? 'ORDER BY ' . $sortArr[$data['sort']] : '';
        $search = !empty($data['search']) ? $data['search'] : '';
        $status_id = !empty($data['status_id']) ? $data['status_id'] : '';
        $client_id = !empty($data['client_id']) ? $data['client_id'] : '';
        $service_id = !empty($data['service_id']) ? $data['service_id'] : '';


        $where = '';
        $join = 'INNER JOIN project_status as ps ON ps.id=p.project_status_id ';
        $join .=  'INNER JOIN services as s ON s.id=p.service_id ';
        $join .= 'INNER JOIN clients as c ON c.id=p.client_id';

        if(!empty($status_id) || !empty($search) || !empty($client_id) || !empty($service_id)) {

            $where = 'WHERE ';

            if(!empty($status_id)) {

                if(is_array($status_id)) {

                    $where .= '(';

                    foreach($status_id as $id) {

                        $where.= "project_status_id=$id OR ";

                    }

                    $where = rtrim($where, 'OR ') . ') AND ';

                } else {
                    $where .= "project_status_id=$status_id AND ";
                }


            }

            if(!empty($client_id)) {


                if(is_array($client_id)) {

                    $where .= '(';

                    foreach($client_id as $id) {

                        $where.= "client_id=$id OR ";

                    }

                    $where = rtrim($where, 'OR ') . ') AND ';

                } else {
                    $where .= "client_id=$client_id AND ";
                }

            }

            if(!empty($service_id)) {


                if(is_array($service_id)) {

                    $where .= '(';

                    foreach($service_id as $id) {

                        $where.= "service_id=$id OR ";

                    }

                    $where = rtrim($where, 'OR ') . ') AND ';

                } else {
                    $where .= "service_id=$service_id AND ";
                }

            }
            
            if(!empty($search)) $where .= "p.name LIKE '%$search%'";

            $where =  rtrim($where, ' AND ');

        }


        $query = "SELECT p.id, p.name, p.creation_date, p.end_date, s.name as service, c.name as client, ps.name as status FROM projects as p $join $where $order LIMIT $page, $limit";

        return $this->query($query);

    }

    public function getProject($id) {

        $query = "SELECT c.id as TABLE_client_TABLE_id, c.name as TABLE_client_TABLE_name, p.id, p.creation_date, 
                  p.end_date, p.name, s.name as TABLE_service_TABLE_name, s.id as TABLE_service_TABLE_id,
                  ppc.alias, com.date as TABLE_comments_TABLE_date, com.id as TABLE_comments_TABLE_id, com.text as TABLE_comments_TABLE_text, 
                  com.admin_id as TABLE_comments_TABLE_admin_id, a.name as TABLE_comments_TABLE_admin_name, 
                  a.image as TABLE_comments_TABLE_admin_image, ps.id as TABLE_status_TABLE_id, ps.name as TABLE_status_TABLE_name FROM projects as p 
                  INNER JOIN clients as c ON c.id=p.client_id
                  INNER JOIN project_status as ps ON ps.id=p.project_status_id
                  INNER JOIN services as s ON s.id=p.service_id 
                  LEFT JOIN project_page_content as ppc ON ppc.id=project_page_content_id
                  LEFT JOIN comments as com ON com.project_id=p.id
                  LEFT JOIN admins as a ON a.id=com.admin_id
                  WHERE p.id=$id";

        $response = $this->query($query);

        $result = [];

        if(!empty($response)) $result = $this->separateData($response);
        else throw new RouteException('The project doesn\'t exist', 404);

       $result['client'] = $result['client'][0];
       $result['service'] = $result['service'][0];
       $result['status'] = $result['status'][0];

        return $result;

    }

    public function createComments($data) {

        $fields = '(';
        $values = '';

        foreach ($data as $key => $arr) {

            $values .= '(';

            foreach($arr as $name_field => $value) {

                if($key === 0) $fields .= "`$name_field`, ";

                $values .= "'$value', ";

            }

            $values = rtrim($values, ', ') . '), ';

        }

        $fields = rtrim($fields, ', ') . ')';
        $values = rtrim($values, ', ');

        $query = "INSERT INTO comments $fields VALUES $values";

        return $this->query($query, 'u', true);

    }

    public function updateComments($data) {

        foreach ($data as $arr) {

            $query = 'UPDATE comments SET ';

            $where = 'WHERE ';

            foreach ($arr as $key => $value) {

                if($key === 'id') {

                    $where .= "id=$value";

                    continue;
                }

                $query .= "`$key`='$value', ";

            }

            $query = rtrim($query, ', ') . $where;

            $this->query($query, 'u');

        }

        return true;

    }

    public function removeComments($data) {

        $where = 'WHERE ' . array_reduce($data, function ($ac, $cur) {

            $id = $cur['id'];

            return $ac . "id='$id' OR ";

        }, '');

        $where = rtrim($where ,' OR ');

        $query = "DELETE FROM comments $where";

        $this->query($query, 'd');

        return true;

    }


}