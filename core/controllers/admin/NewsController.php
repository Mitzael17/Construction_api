<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\NewsModel;

class NewsController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $this->checkAccess('work_with_news');

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = NewsModel::instance();

        $this->$method($args);

    }

    private function get($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $result = $this->model->get('news', $id);

            if(!empty($result['image'])) $result['image'] = $this->createAliasForImage($result['image']);

            exit(json_encode($result));

        }

        $result = $this->model->getNews($_GET);

        if(!empty($result)) {

            foreach ($result as $key => $value) {

                if(!empty($value['image'])) $result[$key]['image'] = $this->createAliasForImage($value['image']);

            }

        }

        exit(json_encode($result));

    }

    private function post($args) {

        $id = isset($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $data = $this->filterData($_POST, [
                'name' => ['optional'],
                'image' => ['optional'],
                'text' => ['optional']
            ]);

            if(empty($data)) throw new ApiException('The data was not provided!', 400);

            $this->model->update('news', $id, $data);

            $name = isset($data['name']) ? $data['name'] : $this->model->get('news', $id)['name'];

            $this->createLog("updated the $name in news");

            exit(json_encode(['status' => 'success']));

        }

        $data = $this->filterData($_POST, [
            'name' => ['necessary'],
            'image' => ['optional'],
            'text' => ['necessary'],
            'date' => ['optional', date('Y-m-d')],
            'make_newsletter' => ['optional', false]
        ]);

        if($data['make_newsletter']) {

            $subscribers = $this->model->getSubscribers();

            if(!empty($subscribers)) {

                ['subject' => $subject, 'message' => $message] = $this->filterData($_POST, [
                    'message' => ['necessary'],
                    'subject' => ['necessary']
                ]);

                foreach ($subscribers as $subscriber) {

                    $message = str_replace('[NS]', $subscriber['surname'] . $subscriber['name'], $message);

                    $this->sendMessage($subscriber['email'], $subject, $message);

                }

            }

        }

        unset($data['make_newsletter']);

        $id = $this->model->create('news', $data);

        $name = $data['name'];

        $this->createLog("created a new record in news ($name)");

        exit(json_encode(['id' => $id]));

    }

    private function delete() {

        $arr_id = $this->getDeleteArrId();

        if(empty($arr_id)) throw new ApiException('The data wasn\'t provided');

        $names = $this->model->getNames('news', $arr_id);

        $this->model->delete('news', $arr_id);

        $names = array_reduce($names, function ($ac, $cur) {

            return $ac . $cur['name'] . ', ';

        }, '');


        $names = rtrim($names, ', ');

        $this->createLog("removed the news: $names");

        exit(json_encode(['status' => 'success']));

    }

}