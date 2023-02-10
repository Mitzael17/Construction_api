<?php

namespace core\models;

use core\traits\SingleTon;

class TestModel extends BaseModel
{

    use SingleTon;

    public function get() {
        exit('it is get');
    }

}