<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\ClientModel;
use core\models\admin\ProjectModel;


class ClientsController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = ClientModel::instance();

        $this->$method($args);

    }

    private function get($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $result = $this->model->getClient($id);

            if(!empty($result['image'])) $result['image'] = $this->createAliasForImage($result['image']);

            $result['projects'] = ProjectModel::instance()->getProjects(['client_id' => $id]);

            exit(json_encode($result));

        }

        $result = $this->model->getClients($_GET);

        if(!empty($result)) {

            foreach ($result as $key => $client) {

                if(!empty($client['image'])) $result[$key]['image'] = $this->createAliasForImage($client['image']);

            }

        }

        exit(json_encode($result));

    }

    private function post($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $data = $this->filterData($_POST, [
                'name' => ['optional'],
                'email' => ['optional'],
                'entity' => ['optional'],
                'image' => ['optional']
            ]);

            if(empty($data)) throw new ApiException('The data was not provided!', 400);

            $this->model->updateClient($id, $data);

            $data = ['status' => 'success'];

            exit(json_encode($data));

        }

        $data = $this->filterData($_POST, [
            'name' => ['necessary'],
            'email' => ['optional'],
            'entity' => ['necessary'],
            'image' => ['optional']
        ]);

        $id = $this->model->createClient($data);

        $data = ['id' => $id];

        exit(json_encode($data));

    }

    private function delete($args) {

        $arr_id = $this->getDeleteArrId();

        $not_deleted_service_id = [];

        $length_arr_id = count($arr_id);

        foreach ($arr_id as $id) {

            $result = $this->model->checkFkRelations($id, [['table' => 'projects', 'foreign_key' => 'client_id']]);

            if(isset($result['projects'])) {

                if($length_arr_id === 1) throw new ApiException("The client can't be removed, because he/she has projects!");

                unset($arr_id[array_search($id, $arr_id)]);

                $not_deleted_service_id[] = $id;

            }

        }

        if(empty($arr_id)) throw new ApiException("The clients have projects");

        $this->model->delete('clients', $arr_id);

        if(empty($not_deleted_service_id)) $data = ['status' => 'success'];
        else $data = ['status' => 'partial success', 'not removed' => $not_deleted_service_id];

        exit(json_encode($data));

    }

}