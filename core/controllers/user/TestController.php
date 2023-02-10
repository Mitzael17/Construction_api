<?php

namespace core\controllers\user;

use core\controllers\user\BaseUser;

class TestController extends BaseUser
{

    public function inputData() {
        exit('hello from user');
    }

}