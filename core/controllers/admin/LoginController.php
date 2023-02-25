<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\LoginModel;

class LoginController extends BaseAdmin
{

    public function inputData() {

        $method = strtolower($_SERVER['REQUEST_METHOD']);

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = LoginModel::instance();

        $this->$method();

    }

    private function post() {

        $data = $this->filterData($_POST, [
            'name' => ['necessary'],
            'password' => ['necessary']
        ]);

        $ip = $_SERVER['REMOTE_ADDR'];

        $attempts = $this->model->getAttempts($ip);

        if($attempts >= LIMIT_ATTEMPTS) {

            exit(json_encode(['status' => 'error', 'message' => 'The limit of attempts was exceeded']));

        }

        $account = $this->model->getAccount($data['name']);

        if(!empty($account) && $data['password'] === $this->decrypt($account['password'])) {

            $this->model->clearAttempts($ip);

            $data = [
                'status' => 'success',
                'token' => $this->generateJWT($account['id'], $account['password']),
            ];

            exit(json_encode($data));

        }

        $date = date('Y-m-d H:i:s');

        $this->model->increaseAttempts($ip, $date);

        exit(json_encode(['status' => 'error', 'message' => 'Name or password is invalid']));

    }

}