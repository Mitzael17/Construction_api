<?php

namespace core\controllers\admin;

use core\exceptions\RouteException;
use core\models\admin\ProjectModel;

class ProjectsController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new RouteException('The url is invalid.', 404);

        $this->model = ProjectModel::instance();

        $this->$method($args);

    }

    private function get($args) {

        $id = !empty($args[0]) ? $args[0] : '';

        if(!empty($id)) {



        }

        $data = $this->model->getProjects($_GET);

        exit(json_encode($data));

    }

}