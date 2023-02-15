<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\models\admin\ProjectModel;

class ProjectsController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->model = ProjectModel::instance();

        $this->$method($args);

    }


    private function get($args) {

        $this->checkRole('project_watching');

        $id = !empty($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $data = $this->model->getProject($id);

            exit(json_encode($data));

        }

        $data = $this->model->getProjects($_GET);

        exit(json_encode($data));

    }

    private function post($args) {

        $id = !empty($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $project_args = $this->filterData($_POST, [
                'name' => ['optional'],
                'service_id' => ['optional'],
                'client_id' => ['optional'],
                'project_status_id' => ['optional'],
                'end_date' => ['optional']
            ]);

            $new_comment_args = isset($_POST['new_comments']) && !empty($_POST['new_comments']) ? $_POST['new_comments'] : '';
            $edited_comment_args = isset($_POST['edited_comments']) && !empty($_POST['edited_comments']) ? $_POST['edited_comments'] : '';
            $removed_comment_args = isset($_POST['removed_comments']) && !empty($_POST['removed_comments']) ? $_POST['removed_comments'] : '';

            if(!empty($new_comment_args)) {

                $arr = [];

                foreach ($new_comment_args as $value) {

                    $arr[] = $this->filterData($value, [
                        'project_id' => ['optional', $id],
                        'text' => ['necessary'],
                        'date' => ['optional', date('Y-m-d H:i:s')],
                        'edited' => ['optional', 0]
                    ]);

                }

                $new_comment_args = $arr;

            }

            if(!empty($edited_comment_args)) {

                $arr = [];

                foreach ($edited_comment_args as $value) {

                    $arr[] = $this->filterData($value, [
                        'id' => ['necessary'],
                        'text' => ['necessary'],
                        'edited' => ['optional', 1]
                    ]);

                }

                $edited_comment_args = $arr;

            }

            if(!empty($removed_comment_args)) {

                $arr = [];

                foreach ($removed_comment_args as $value) {

                    $arr[] = $this->filterData($value, [
                        'id' => ['necessary']
                    ]);

                }

                $removed_comment_args = $arr;

            }
            
            if(empty($new_comment_args) && empty($edited_comment_args) && empty($removed_comment_args) && empty($project_args)) throw new ApiException('The request doesn\'t have any information');

            if(!empty($project_args)) $this->model->update('projects', $id, $project_args);
            if(!empty($new_comment_args)) $this->model->createComments($new_comment_args);
            if(!empty($edited_comment_args)) $this->model->updateComments($edited_comment_args);
            if(!empty($removed_comment_args)) $this->model->removeComments($removed_comment_args);

            $data = ['status' => 'success'];

            exit(json_encode($data));

        }

        $args = $this->filterData($_POST, [
            'name' => ['necessary'],
            'service_id' => ['necessary'],
            'client_id' => ['necessary'],
            'project_status_id' => ['optional', DEFAULT_STATUS_ID],
            'creation_date' => ['optional', date('y-m-d')],
        ]);

        $id = $this->model->create('projects', $args);

        http_response_code(201);

        $data = ['id' => $id];

        exit(json_encode($data));

    }

    private function delete() {

        $arr_id = $this->getDeleteArrId();

        $this->model->delete('projects', $arr_id);

        $data = ['status' => 'success'];

        exit(json_encode($data));

    }

}