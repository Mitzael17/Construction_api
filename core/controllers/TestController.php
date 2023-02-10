<?php

namespace core\controllers;

use core\models\TestModel;

class TestController extends BaseController
{

    public function inputData() {

        $this->test_model = TestModel::instance();

        $this->test_model->get();

    }

}