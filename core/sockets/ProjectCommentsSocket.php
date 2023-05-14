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

        $message = null;

        if(!is_array($data) || empty($data['data'])) return null;

        if($data['type'] === 'create') $message = $this->createComment($data['data']);
        if($data['type'] === 'delete') $message = $this->deleteComment($data['data']);
        if($data['type'] === 'update') $message = $this->updateComment($data['data']);

        if(!$message) return;

        foreach ($this->users as $userConnection) $userConnection->send(json_encode($message));

    }

    private function createComment(array $data) {

        $data['date'] = date('Y-m-d H:i:s');
        $data['text'] = addslashes($data['text']);

        $id = $this->model->createComments([$data]);
        $admin = $this->model->get('admins', $data['admin_id']);

        if (!$id || empty($admin)) return null;

        return [
            'type' => 'create',
            'data' => [
                'id' => $id,
                'text' => stripslashes($data['text']),
                'date' => $data['date'],
                'admin_id' => (int) $admin['id'],
                'admin_name' => $admin['name'],
                'admin_image' => $this->createLinkForImage($admin['image'])
            ]
        ];

    }

    private function deleteComment(array $data) {

        $id = $data['id'];

        if(!$id) return null;

        $this->model->removeComments([['id' => $id]]);

        return [
            'type' => 'delete',
            'data' => [
                'id' => (int) $id
            ]
        ];

    }

    private function updateComment(array $data) {

        $data['text'] = addslashes($data['text']);
        $this->model->updateComments([['id' => $data['id'], 'text' => $data['text']]]);

        return [
            'type' => 'update',
            'data' => [
                'id' => (int) $data['id'],
                'text' => $data['text']
            ]
        ];

    }

}

