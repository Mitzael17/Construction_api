<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\LogModel;

class LogsController extends BaseAdmin
{

    public function inputData() {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = LogModel::instance();

        $this->$method();

    }

    private function get() {

        $result = $this->model->getLogs($_GET);

        foreach ($result as $key => $arr) {

            if(empty($arr['admin']['image'])) continue;

            $result[$key]['admin']['image'] = $this->createAliasForImage($arr['admin']['image']);

        }

        exit(json_encode($result));

    }

}