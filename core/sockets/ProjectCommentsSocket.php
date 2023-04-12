<?php

namespace core\sockets;

use core\controllers\base\SingleTon;
use core\models\admin\ProjectModel;
use Workerman\Worker;

class ProjectCommentsSocket extends BaseSocket {

    use SingleTon;

    private Worker $worker;
    private string $port = '8001';

    private ProjectModel $model;

    public function connect() {

        $this->model = ProjectModel::instance();

        $this->worker = new Worker("$this->address:$this->port");

        $this->worker->count = 4;

        $this->worker->onConnect = fn($construction) => $this->onConnect($construction);
        $this->worker->onClose = fn($construction) => $this->onClose($construction);
        $this->worker->onMessage = fn($connection, $data) => $this->onMessage($connection, $data);

        Worker::runAll();

    }

    private function onMessage($connection, $data) {

        if(empty($data)) return;

        $data = json_decode($data, true);

        $data['date'] = date('Y-m-d H:i:s');
        $data['text'] = addslashes($data['text']);

        $id = $this->model->createComments([$data]);
        $admin = $this->model->get('admins', $data['admin_id']);

        if (!$id || empty($admin)) return;

        $message = [
            'id' => $id,
            'text' => stripslashes($data['text']),
            'date' => $data['date'],
            'admin_id' => $admin['id'],
            'admin_name' => $admin['name'],
            'admin_image' => $this->createLinkForImage($admin['image'])
        ];

        foreach ($this->users as $userConnection) $userConnection->send(json_encode($message));

    }

}

