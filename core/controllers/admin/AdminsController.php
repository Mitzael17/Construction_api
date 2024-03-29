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

        if(isset($_GET['check_name'])) $this->checkName();

        if(!empty($id)) {

            if($this->user['id'] !== $id && !$this->checkAccess('work_with_admins', true)) throw new ApiException('Access denied', 403);

            $result = $this->model->getAdmin($id);

            if(empty($result)) exit(json_encode($result));

            if($this->user['id'] === $id) $result['password'] = $this->decrypt($result['password']);
            else unset($result['password']);

            if(!empty($result['image'])) $result['image'] = $this->createLinkForImage($result['image']);

            $result = $this->stringFieldsToInt($result);

            exit(json_encode($result));

        }

        $this->checkAccess('work_with_admins');

        $data = $this->filterData($_GET, [
            'limit' => ['optional'],
            'page' => ['optional'],
            'search' => ['optional']
        ]);

        $data['priority'] = $this->user['priority'];

        $result = $this->model->getAdmins($data);

        foreach ($result as $key => $arr) {

            if(!isset($arr['image']) || empty($arr['image'])) continue;

            $result[$key]['image'] = $this->createLinkForImage($arr['image']);

        }

        $result = $this->stringFieldsToInt($result);

        exit(json_encode($result));

    }

    private function post($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $user = $this->model->getAdmin($id);

            if(empty($user)) throw new ApiException('The admin wasn\'t found', 404);

            if($this->user['id'] !== $id) {

                $this->checkAccess('work_with_admins');

                if($this->user['priority'] >= $user['priority']) throw new ApiException('Access denied', 403);

            }

            $data = $this->filterData($_POST, [
                'name' => ['optional'],
                'image' => ['optional'],
                'password' => ['optional'],
                'role_id' => ['optional'],
                'blocked' => ['optional']
            ]);

            if(empty($data)) throw new ApiException('The data was not provided!', 400);

            if(!empty($data['role_id'])) {

                $role = $this->model->get('roles', $data['role_id']);

                if(empty($role)) throw new ApiException('The role doesn\'t exist');

                if($role['priority'] <= $this->user['priority']) throw new ApiException('Access denied. The role is unavailable for you.', 403);

            }

            if(!empty($data['name']) && $this->model->checkUniqueField('admins', 'name', $data['name'])) throw new ApiException('The name is already busy');

            if(!empty($data['password'])) $data['password'] = $this->encrypt($data['password']);

            $this->model->update('admins', $id, $data);

            $data = ['status' => 'success'];

            exit(json_encode($data));

        }

        $this->checkAccess('work_with_admins');

        $data = $this->filterData($_POST, [
            'name' => ['necessary'],
            'image' => ['optional'],
            'password' => ['necessary'],
            'role_id' => ['necessary'],
            'blocked' => ['optional', 0]
        ]);



        $role = $this->model->get('roles', $data['role_id']);

        if(empty($role)) throw new ApiException('The role doesn\'t exist');

        if($role['priority'] <= $this->user['priority']) throw new ApiException('Access denied. The role is unavailable for you.', 403);


        if($this->model->checkUniqueField('admins', 'name', $data['name'])) throw new ApiException('The name is already busy');

        $data['password'] = $this->encrypt($data['password']);

        $id = $this->model->create('admins', $data);

        $this->createLog('created a new admin - ' . $data['name']);

        $data = ['status' => 'success', 'id' => $id];

        exit(json_encode($data));

    }

    private function delete($args) {

        $this->checkAccess('work_with_admins');

        $arr_id = $this->getDeleteArrId();

        $users = $this->model->getPriorityAdmins($arr_id);

        foreach ($users as $user) {

            if($user['priority'] <= $this->user['priority']) throw new ApiException('Access denied', 403);

        }

        $not_removed = '';

        foreach ($arr_id as $key => $id) {

            $isFk = $this->model->checkFkRelations($id, [['foreign_key' => 'admin_id', 'table' => 'comments']]);

            if(isset($isFk['comments'])) {

                unset($arr_id[$key]);

                foreach ($users as $user_key => $user) {

                    if($user['id'] !== $id) continue;

                    $not_removed .= $user['name'] . ', ';

                    unset($users[$user_key]);

                }

            }

        }

        if(empty($arr_id)) {

            $data = [
                'status' => 'error',
                'message' => 'The admin can\'t be removed, because he/she created one or more comments'
            ];

            exit(json_encode($data));

        }

        $toBe = count($users) > 1 ? 'were' : 'was';

        $order_names = rtrim(array_reduce($users, function ($ac, $cur) {
            $name = $cur['name'];
            return $ac . "$name, ";
        }, ''), ', ');


        $this->model->delete('admins', $arr_id);

        $this->createLog("$order_names $toBe removed from admins table");

        if(!empty($not_removed)) {

            $not_removed = rtrim($not_removed, ', ');

            $data = [
                'status' => 'warning',
                'message' => $not_removed
            ];

            exit(json_encode($data));

        }

        $data = ['status' => 'success'];

        exit(json_encode($data));

    }

    private function checkName() {

        $result = $this->model->getByData('admins', ['name' => $_GET['check_name']]);

        if(empty($result) || $result[0]['id'] === $this->user['id']) exit(json_encode(['status' => 'success']));

        exit(json_encode(['status' => 'error', 'message' => 'The name is already busy']));
    }

}