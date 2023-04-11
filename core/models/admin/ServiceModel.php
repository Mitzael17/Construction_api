<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;
use core\exceptions\ApiException;

class ServiceModel extends \core\models\base\BaseModel
{

    use SingleTon;

    public function getServices($data) {

        $limit = !empty($data['limit']) ? $data['limit'] : 20;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $limit : 0;
        $search = !empty($data['search']) ? $data['search'] : '';

        $where = '';

        $join = 'LEFT JOIN service_page_content as spc ON spc.id=s.service_page_id ';

        if(!empty($search)) $where = "WHERE s.name LIKE '%$search%'";

        $query = "SELECT s.id, s.name, spc.alias  FROM services as s $join $where LIMIT $page, $limit";

        return $this->query($query);

    }

    public function getService($id) {

        $result = $this->query("SELECT s.id, s.name, spc.alias
                                    FROM services as s LEFT JOIN service_page_content as spc ON spc.id=s.service_page_id 
                                    WHERE s.id=$id");

        $result = $this->separateData($result);

        return $result;

    }

    public function getServicePage(int $id) {

        $result = $this->query("SELECT spc.*, s.id, spc.alias FROM service_page_content as spc INNER JOIN services as s ON s.id=spc.id WHERE s.id=$id");

        if(isset($result[0])) return $result[0];

        throw new ApiException('The service page does\'nt exist!', 404);

    }


}