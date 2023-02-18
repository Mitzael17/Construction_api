<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;

class MessageModel extends \core\models\base\BaseModel
{

    use SingleTon;

    public function getMessages($data) {

        $sortArr = [
            'newest' => 'id DESC',
            'oldest' => 'id'
        ];

        $limit = !empty($data['limit']) ? $data['limit'] : 20;
        $page = !empty($data['page']) ? ($data['page'] - 1) * $data['limit'] : 0;
        $order = !empty($data['sort']) ? $data['sort'] : 'newest';
        $search = !empty($data['search']) ? $data['search'] : '';
        $from = !empty($data['date_from']) ? $data['date_from'] : '';
        $to = !empty($data['date_to']) ? $data['date_to'] : '';

        $where_value_date = '';

        if(!empty($from)) $where_value_date .= " m.date>='$from' AND ";
        if(!empty($to)) $where_value_date .= " m.date<='$to'";

        $where = '';

        if(!empty($search)) $where = (!empty($where_value_date) ? rtrim($where_value_date, 'AND ') . ' AND ' : '') . " (m.name LIKE '%$search%' OR m.email LIKE '%$search%')";
        elseif(!empty($where_value_date)) $where = rtrim($where_value_date, 'AND ');

        $query = '';

        if(!empty($order)) {

            if($order === 'read' || $order === 'unread' || $order === 'answered') {

                if($order === 'unread') $ms_id = 1;
                if($order === 'read') $ms_id = 2;
                if($order === 'answered') $ms_id = 3;

                if(!empty($where)) $where = ' AND ' . $where;

                $query = "SELECT m.id, m.name, m.email, m.date, ms.name as status FROM messages as m 
                              INNER JOIN message_status as ms ON ms.id=m.message_status_id WHERE m.message_status_id='$ms_id' $where UNION 
                              SELECT m.id, m.name, m.email, m.date, ms.name as status FROM messages as m 
                              INNER JOIN message_status as ms ON ms.id=m.message_status_id WHERE m.message_status_id<>'$ms_id' $where LIMIT $page, $limit";

            } else {

                if(isset($sortArr[$order])) $order = 'ORDER BY ' . $sortArr[$order];

                if(!empty($where)) $where = 'WHERE ' . $where;

                $query = "SELECT m.id, m.name, m.email, m.date, ms.name as status FROM messages as m 
                          INNER JOIN message_status as ms ON ms.id=m.message_status_id $where $order LIMIT $page, $limit";

            }


        } else {

            if(!empty($where)) $where = 'WHERE ' . $where;

            $query = "SELECT m.id, m.name, m.email, m.date, ms.name as status FROM messages as m 
                          INNER JOIN message_status as ms ON ms.id=m.message_status_id $where LIMIT $page, $limit";

        }


        return $this->query($query);

    }

    public function getMessage($id) {

        $result = $this->query("SELECT m.id, m.name, m.email, m.message, m.date
                                    FROM messages as m WHERE m.id='$id'");

        if(isset($result[0])) {

            $this->query("UPDATE messages SET message_status_id=2 WHERE id='$id'", 'u');

            return $result[0];
        }

        return $result;

    }

}