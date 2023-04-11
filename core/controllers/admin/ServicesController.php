<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\ServiceModel;

class ServicesController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = ServiceModel::instance();

        $this->$method($args);

    }

    private function get($args) {

        $id = '';
        $isPageRequest = $args[0] === 'page';

        if($isPageRequest) $id = $args[1];
        else $id = $args[0];

        if(!empty($id)) {

            $result = [];

            if($isPageRequest) $result = $this->model->getServicePage($id);
            else $result = $this->model->getService($id);

            $result = $this->stringFieldsToInt($result);

            exit(json_encode($result));

        }

        $result = $this->model->getServices($_GET);

        $result = $this->stringFieldsToInt($result);

        exit(json_encode($result));

    }

    private function post($args) {

        $this->checkAccess('service_edit');

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $data = $this->filterData($_POST, [
                'name' => ['optional']
            ]);

            if(empty($data)) throw new ApiException('The data was not provided!', 400);

            if($this->model->checkUniqueField('services', 'name', $data['name'])) throw new ApiException('The name is already busy');

            $this->model->update('services', $id, $data);

            $name = $data['name'];

            $this->createLog("$name was updated");

            $data = ['status' => 'success'];

            exit(json_encode($data));

        }

        $data = $this->filterData($_POST, [
            'name' => ['necessary']
        ]);

        if($this->model->checkUniqueField('services', 'name', $data['name'])) throw new ApiException('The name is already busy');

        $id = $this->model->create('services', $data);

        $this->createLog('created a new service - ' . $data['name']);

        $data = ['status' => 'success', 'id' => $id];

        exit(json_encode($data));

    }

    private function delete() {

        $this->checkAccess('service_edit');

        $arr_id = $this->getDeleteArrId();

        $not_deleted_service_id = [];

        $length_arr_id = count($arr_id);

        foreach ($arr_id as $id) {

            $result = $this->model->checkFkRelations($id, [['table' => 'projects', 'foreign_key' => 'service_id']]);

            if(isset($result['projects'])) {

                if($length_arr_id === 1) throw new ApiException("The service can't be removed, because some projects refer to the service!");

                unset($arr_id[array_search($id, $arr_id)]);

                $not_deleted_service_id[] = $id;

            }

        }

        if(empty($arr_id)) throw new ApiException("The services have projects");

        $order_names = $this->model->getNames('services', $arr_id);

        $toBe = count($order_names) > 1 ? 'were' : 'was';

        $order_names = rtrim(array_reduce($order_names, function ($ac, $cur) {
            $name = $cur['name'];
            return $ac . "$name, ";
        }, ''), ', ');

        $this->model->delete('services', $arr_id);

        $this->createLog("$order_names $toBe removed from services");

        if(empty($not_deleted_service_id)) $data = ['status' => 'success'];
        else $data = ['status' => 'warning', 'message' => $not_deleted_service_id];

        exit(json_encode($data));

    }
}