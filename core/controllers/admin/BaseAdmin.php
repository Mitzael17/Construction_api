<?php

namespace core\controllers\admin;

use core\controllers\base\BaseController;
use core\exceptions\ApiException;

abstract class BaseAdmin extends BaseController
{

    protected $method;

    protected function init() {

        $this->method = $_SERVER['REQUEST_METHOD'];

    }

    protected function filterData($data, $fieldsArr) {

        $result = [];

        foreach ($data as $key => $value) {

            if(!array_key_exists($key, $fieldsArr)) continue;

            $result[$key] = $value;

        }

        $empty_fields = '';

        foreach ($fieldsArr as $key => $value) {

            if(!array_key_exists($key, $result)) {

                if($value[0] === 'necessary') {

                    $empty_fields .= "$key, ";

                    continue;

                }

                if(isset($value[1])) $result[$key] = $value[1];

            };

        }


        if(!empty($empty_fields)) {

            $empty_fields = rtrim($empty_fields, ', ');

            throw new ApiException("The request must contain the parameters: $empty_fields", 400);

        }

        return $result;
    }

}