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

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $result = $this->model->getService($id);

            exit(json_encode($result));

        }

        $result = $this->model->getServices($_GET);

        exit(json_encode($result));

    }

    private function post($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $data = $this->filterData($_POST, [
                'name' => ['optional']
            ]);

            if(empty($data)) throw new ApiException('The data was not provided!', 400);

            $this->model->updateService($id, $data);

            $data = ['status' => 'success'];

            exit(json_encode($data));

        }

        $data = $this->filterData($_POST, [
            'name' => ['necessary']
        ]);

        $id = $this->model->createService($data);

        $data = ['id' => $id];

        exit(json_encode($data));

    }

    private function delete() {

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

        $this->model->delete('services', $arr_id);

        if(empty($not_deleted_service_id)) $data = ['status' => 'success'];
        else $data = ['status' => 'partial success', 'not removed' => $not_deleted_service_id];

        exit(json_encode($data));

    }
}