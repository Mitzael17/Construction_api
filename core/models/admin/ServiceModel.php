<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;

class ServiceModel extends \core\models\base\BaseModel
{

    use SingleTon;

    public function getServices($data) {

        $limit = !empty($data['limit']) ? $data['limit'] : 20;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $data['limit'] : 0;
        $search = !empty($data['search']) ? $data['search'] : '';

        $where = '';

        $join = 'LEFT JOIN service_page_content as spc ON spc.id=s.service_page_id ';

        if(!empty($search)) $where = "WHERE s.name LIKE '%$search%'";

        $query = "SELECT s.id, s.name, spc.alias  FROM services as s $join $where LIMIT $page, $limit";

        return $this->query($query);

    }

    public function getService($id) {

        $result = $this->query("SELECT s.id, s.name, 
                                    spc.id as TABLE_service_page_content_TABLE_id, spc.alias as TABLE_service_page_content_TABLE_alias 
                                    FROM services as s LEFT JOIN service_page_content as spc ON spc.id=s.service_page_id 
                                    WHERE s.id=$id");

        $result = $this->separateData($result);

        return $result;

    }

    public function createService($data) {

        $values = $this->createInsertValues($data);

        $query = "INSERT INTO services $values";

        return $this->query($query, 'u', true);

    }

    public function updateService($id, $data) {

        $update = $this->createUpdate($data);

        $this->query("UPDATE services $update WHERE id=$id", 'u');

        return true;

    }

}