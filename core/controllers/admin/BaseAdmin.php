<?php

namespace core\controllers\admin;

use core\controllers\base\BaseController;
use core\exceptions\ApiException;
use core\libraries\Firebase\JWT;
use core\libraries\Firebase\Key;
use core\models\base\BaseModel;

abstract class BaseAdmin extends BaseController
{

    protected $method;

    protected $user = [];
    protected $base_model;

    protected function init() {

        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        if($this->method === 'options') exit(['status' => 'ok']);

        $this->protocol = $_SERVER['REQUEST_SCHEME'];

        $this->base_model = BaseModel::instance();

        $userData = $this->getUserDataFromToken();

        $data = $this->base_model->query("SELECT r.*, a.blocked, a.id FROM roles as r INNER JOIN admins as a ON a.role_id=r.id WHERE a.id='$userData->id' AND password='$userData->password'");

        if(empty($data)) throw new ApiException('Auth error', 403);

        $this->user = $data[0];

        if($this->user['blocked']) throw new ApiException('The account is blocked', 403);

    }

    protected function filterData($data, $fieldsArr) {

        $result = [];

        foreach ($data as $key => $value) {

            if(!array_key_exists($key, $fieldsArr)) continue;

            if(is_string($value)) $result[$key] = addslashes($value);
            elseif(is_array($value)) $result[$key] = $this->addslashesForArray($value);
            else $result[$key] = $value;

        }

        $empty_fields = '';

        foreach ($fieldsArr as $key => $value) {

            if(!array_key_exists($key, $result)) {

                if($value[0] === 'necessary') {

                    $empty_fields .= "$key, ";

                    continue;

                }

                if(isset($value[1])) $result[$key] = addslashes($value[1]);

            };

        }


        if(!empty($empty_fields)) {

            $empty_fields = rtrim($empty_fields, ', ');

            throw new ApiException("The request must contain the parameters: $empty_fields", 400);

        }

        return $result;
    }

    protected function getDeleteArrId() {

        $arr_parameters = $this->getDeleteData();

        return $this->filterData($arr_parameters, [
            'id' => ['necessary']
        ])['id'];

    }

    protected function getDeleteData() {

        if($this->method !== 'delete') throw new ApiException('the method is only for delete protocol.');

        $arr_parameters = [];

        parse_str($this->getContent(), $arr_parameters );

        return $arr_parameters;

    }

    private function getContent() {

        $content = file_get_contents('php://input');

        if (null === $content)
        {
            if (0 === strlen(trim($content = file_get_contents('php://input'))))
            {
                $content = false;
            }
        }

        return $content;

    }

    protected function checkAccess($flag, $return_bool = false) {

        if($return_bool) return (boolean) $this->user[$flag];

        if(!$this->user[$flag]) throw new ApiException('Access denied', 403);

    }

    protected function generateJWT($id, $password) {

        $payload = [
            'iss' => $_SERVER['HTTP_HOST'],
            'aud' => $_SERVER['HTTP_HOST'],
            'exp' => time() + 360000,
            'data' => [
                'id' => $id,
                'password' => $password,
            ],
        ];
        return JWT::encode($payload, JWT_KEY, 'HS256');

    }

    protected function getUserDataFromToken() {


        if(!isset($_SERVER['HTTP_AUTHORIZATION'])) throw new ApiException('you are not logged in', 403);

        $token = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

        if(!isset($token[1])) throw new ApiException('you are not logged in', 403);

        try {

            $decoded = JWT::decode($token[1], new Key(JWT_KEY,'HS256'));

        } catch (\Exception $e) {

            throw new ApiException('you are not logged in', 403);

        }

        return $decoded->data;

    }

    protected function createLog(string $message) {

        $data = [];

        $data['admin_id'] = $this->user['id'];
        $data['date_and_time'] = date('Y-m-d H:i:s');
        $data['message'] = addslashes($message);

        $this->base_model->create('logs', $data);

    }

    protected function sendMessage($to, $subject, $message, $additional_headers = '', $params = '') {

        $from = '';

        $message = wordwrap($message, 70, "\r\n");

        $headers = 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $from . "\r\n"
        . $additional_headers;

        mail($to, $subject, $message, $headers, $params);

    }

}