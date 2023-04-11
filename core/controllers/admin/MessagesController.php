<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\MessageModel;

class MessagesController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $this->checkAccess('work_with_messages');

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = MessageModel::instance();

        $this->$method($args);

    }

    private function get($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $result = $this->model->getMessage($id);

            $result = $this->stringFieldsToInt($result);

            exit(json_encode($result));

        }

        $result = $this->model->getMessages($_GET);

        $result = $this->stringFieldsToInt($result);

        exit(json_encode($result));

    }

    private function post($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(empty($id)) throw new ApiException('The message id wasn\'t provided', 404);

        $data = $this->filterData($_POST, [
            'subject' => ['necessary'],
            'message' => ['necessary']
        ]);

        $messages_info = $this->model->get('messages', $id);

        if(empty($messages_info)) throw new ApiException('The message wasn\'t found', 404);

        $data['to'] = $messages_info['email'];

        $this->sendMessage($data['to'], $data['subject'], $data['message']);

        $this->model->update('messages', $id, ['message_status_id' => '3']);

        $this->createLog('sent a message to ' . $data['to']);

        exit(json_encode(['status' => 'success']));

    }
}