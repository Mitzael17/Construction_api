<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\AdminModel;

class AdminsController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = AdminModel::instance();

        $this->$method($args);

    }

    private function get($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $result = $this->model->getAdmin($id);

            if(!empty($result['image'])) $result['image'] = $this->createAliasForImage($result['image']);

            exit(json_encode($result));

        }

        $data = $this->filterData($_GET, [
            'limit' => ['optional'],
            'page' => ['optional'],
            'search' => ['optional']
        ]);

        $result = $this->model->getAdmins($data);

        foreach ($result as $key => $arr) {

            if(!isset($arr['image']) || empty($arr['image'])) continue;

            $result[$key]['image'] = $this->createAliasForImage($arr['image']);

        }

        exit(json_encode($result));

    }

    private function post($args) {

    }



    private function delete($args) {

    }

}