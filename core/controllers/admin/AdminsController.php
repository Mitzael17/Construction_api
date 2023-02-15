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

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $data = $this->filterData($_POST, [
                'name' => ['optional'],
                'image' => ['optional'],
                'password' => ['optional'],
                'role_id' => ['optional'],
                'blocked' => ['optional']
            ]);

            if(empty($data)) throw new ApiException('The data was not provided!', 400);

            if(!empty($data['name']) && $this->model->checkUniqueField('admins', 'name', $data['name'])) throw new ApiException('The name is already busy');

            if(!empty($data['password'])) $data['password'] = $this->encrypt($data['password']);

            $this->model->update('admins', $id, $data);

            $data = ['status' => 'success'];

            exit(json_encode($data));

        }

        $data = $this->filterData($_POST, [
            'name' => ['necessary'],
            'image' => ['optional'],
            'password' => ['necessary'],
            'role_id' => ['necessary'],
            'blocked' => ['optional', 0]
        ]);

        if($this->model->checkUniqueField('admins', 'name', $data['name'])) throw new ApiException('The name is already busy');

        $data['password'] = $this->encrypt($data['password']);

        $id = $this->model->create('admins', $data);

        $data = ['id' => $id];

        exit(json_encode($data));

    }



    private function delete($args) {

        $arr_id = $this->getDeleteArrId();

        $this->model->delete('admins', $arr_id);

        $data = ['status' => 'success'];

        exit(json_encode($data));

    }

}