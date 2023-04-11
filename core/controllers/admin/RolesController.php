<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\AdminModel;
use core\models\admin\RoleModel;

class RolesController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        $this->checkAccess('work_with_admins');

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = RoleModel::instance();

        $this->$method($args);

    }

    private function get($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $result = $this->model->getRole($id);

            $result = $this->stringFieldsToInt($result);

            exit(json_encode($result));

        }

        $result = $this->model->getRoles($_GET);

        $result = $this->stringFieldsToInt($result);


        exit(json_encode($result));

    }

    private function post($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $data = $this->filterData($_POST, [
                'name' => ['optional'],
                'project_watching' => ['optional'],
                'project_edit' => ['optional'],
                'content_page_management' => ['optional'],
                'service_edit' => ['optional'],
                'work_with_messages' => ['optional'],
                'work_with_admins' => ['optional'],
                'priority' => ['optional'],
                'work_with_clients' => ['optional']
            ]);

            if(empty($data)) throw new ApiException('The data was not provided!', 400);

            $this->checkRoleFlags($data);

            if(isset($data['name']) && $this->model->checkUniqueField('roles', 'name', $data['name'])) throw new ApiException('The name is already busy');

            $this->model->update('roles', $id, $data);

            $name = isset($data['name']) ? $data['name'] : $this->model->get('roles', $id)['name'];

            $this->createLog("$name was updated");

            $data = ['status' => 'success'];

            exit(json_encode($data));

        }

        $data = $this->filterData($_POST, [
            'name' => ['necessary'],
            'project_watching' => ['necessary'],
            'project_edit' => ['necessary'],
            'content_page_management' => ['necessary'],
            'service_edit' => ['necessary'],
            'work_with_messages' => ['necessary'],
            'work_with_admins' => ['necessary'],
            'priority' => ['necessary'],
            'work_with_clients' => ['necessary'],
            'work_with_news' => ['necessary'],
            'work_with_contact_information' => ['necessary']
        ]);

        $this->checkRoleFlags($data);

        if($this->model->checkUniqueField('roles', 'name', $data['name'])) throw new ApiException('The name is already busy');

        $id = $this->model->create('roles', $data);

        $this->createLog('created a new role - ' . $data['name']);

        $data = ['status' => 'success', 'id' => $id];

        $data = $this->stringFieldsToInt($data);

        exit(json_encode($data));

    }

    private function delete() {

        $arr_id = $this->getDeleteArrId();

        $roles = $this->model->getPriorityRoles($arr_id);

        $not_removed = '';

        foreach ($roles as $key => $role) {

            if($role['priority'] <= $this->user['priority']) throw new ApiException('Access denied', 403);

            $isFk = $this->model->checkFkRelations($role['id'], [['foreign_key' => 'role_id', 'table' => 'admins']]);


            if(isset($isFk['admins']) && $isFk['admins']) {

                $name = $role['name'];

                $not_removed .= "$name, ";

                unset($arr_id[array_search($role['id'], $arr_id)]);
                unset($roles[$key]);

            };

        }

        $toBe = count($roles) > 1 ? 'were' : 'was';

        $order_names = rtrim(array_reduce($roles, function ($ac, $cur) {

            $name = $cur['name'];
            return $ac . "$name, ";

        }, ''), ', ');


        if(!empty($arr_id)) {

            $this->model->delete('roles', $arr_id);

            $this->createLog("$order_names $toBe removed from roles table");

        } else {
            exit(json_encode(['status' => 'error', 'message' => 'The role(s) can\'t be removed.']));
        }

        if(!empty($not_removed)) {

            $not_removed = rtrim($not_removed, ', ');

            $data = ['status' => 'warning', 'message' => $not_removed];

        } else {
            $data = ['status' => 'success'];
        }

        exit(json_encode($data));

    }


    private function checkRoleFlags($data) {

        if(isset($data['priority']) && $this->user['priority'] >= $data['priority']) throw new ApiException('You can\'t set role priority equal or higher, than your role priority', 403);

        $available_flags = array_filter($data, function ($item) {

            return $item;

        });

        $unavailable_flags = '';

        foreach ($available_flags as $flag => $value) {

            if($flag === 'name' || $flag === 'priority' || $this->user[$flag]) continue;

            $unavailable_flags .= "$flag, ";

        }

        if(!empty($unavailable_flags)) {

            $unavailable_flags = rtrim($unavailable_flags, ', ');

            throw new ApiException("Access denied. The flags are unavailable for you: $unavailable_flags", 403);
        }

    }
}