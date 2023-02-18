<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;

class NewsModel extends \core\models\base\BaseModel
{

    use SingleTon;

    public function getNews($data) {

        $limit = !empty($data['limit']) ? $data['limit'] : 20;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $data['limit'] : 0;
        $search = !empty($data['search']) ? $data['search'] : '';
        $from = !empty($data['date_from']) ? $data['date_from'] : '';
        $to = !empty($data['date_to']) ? $data['date_to'] : '';

        $where_value = '';

        if(!empty($from)) $where_value .= "date>='$from' AND ";
        if(!empty($to)) $where_value .= "date<='$to' AND ";

        if(!empty($search)) $where_value .= "name LIKE '%$search%'";

        $where = '';

        if(!empty($where_value)) $where = 'WHERE ' . rtrim($where_value, 'AND ');

        return $this->query("SELECT id, name, image, date FROM news $where ORDER BY date DESC LIMIT $page, $limit");

    }

    public function getSubscribers() {

        return $this->query("SELECT * FROM subscribers");

    }

}