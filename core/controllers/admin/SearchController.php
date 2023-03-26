<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\SearchModel;

class SearchController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = SearchModel::instance();

        $this->$method($args);

    }


    private function get() {

        $data = $this->filterData($_GET, [
            'table' => ['necessary'],
            'value' => ['necessary'],
            'field' => ['optional', 'name'],
            'limit' => ['optional', 5]
        ]);

        $result = $this->model->search($data['table'], $data['field'], $data['value'], $data['limit']);

        exit(json_encode($result));

    }

}