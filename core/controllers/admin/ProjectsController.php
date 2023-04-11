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

        $this->checkAccess('project_watching');

        $id = !empty($args[0]) ? $args[0] : '';

        if(!empty($id)) {

            $data = $this->model->getProject($id);

            $data = $this->stringFieldsToInt($data);

            if(!empty($data['comments'])) {

                foreach ($data['comments'] as $key => $comment) {

                    if(empty($comment['admin_image'])) continue;

                    $data['comments'][$key]['admin_image'] = $this->createLinkForImage($comment['admin_image']);

                }

            }

            exit(json_encode($data));

        }

        $data = $this->model->getProjects($_GET);

        $data = $this->stringFieldsToInt($data);

        exit(json_encode($data));

    }

    private function post($args) {

        $this->checkAccess('project_edit');

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

                    $args = $this->filterData($value, [
                        'project_id' => ['optional', $id],
                        'text' => ['necessary'],
                        'date' => ['optional', date('Y-m-d H:i:s')],
                        'edited' => ['optional', 0]
                    ]);

                    $args['admin_id'] = $this->user['id'];

                    $arr[] = $args;

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

            if(empty($project_args['name'])) $name = $this->model->get('projects' , $id)['name'];
            else $name = $project_args['name'];

            $this->createLog('updated the project - ' . $name);

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

        $this->createLog('created a new project - ' . $args['name']);

        http_response_code(201);

        $data = ['status' => 'success', 'id' => $id];

        exit(json_encode($data));

    }

    private function delete() {

        $this->checkAccess('project_edit');

        $arr_id = $this->getDeleteArrId();

        $records = $this->model->get('projects', $arr_id);

        $records = isset($records[0]) ? $records : [$records];


        $order_names = '';
        $not_deleted = '';
        $not_deleted_id = [];

        foreach ($records as $key => $record) {

            if(!empty($record['project_page_content_id'])) {

                unset($arr_id[array_search($record['id'], $arr_id)]);
                $not_deleted .= $record['name'] . ', ';
                $not_deleted_id = $record['id'];
                unset($records[$key]);

            }

        }

        if(!empty($records)) {

            $order_names = rtrim(array_reduce($records, function ($ac, $cur) {
                $name = $cur['name'];
                return $ac . "$name, ";
            }, ''), ', ');

        }

        if(empty($arr_id)) {

            exit(json_encode([
                'status' => 'error',
                'message' => 'The project(s) can\'t be removed',
                'arr_id' => $not_deleted_id
            ]));

        }

        $toBe = count($records) > 1 ? 'were' : 'was';

        $this->model->delete('projects', $arr_id);

        $this->createLog("$order_names $toBe removed from projects");

        if(!empty($not_deleted)) {

            $not_deleted = rtrim($not_deleted, ', ');

            exit(json_encode([
                'status' => 'warning',
                'message' => 'The projects can\'t be removed: ' . $not_deleted,
                'arr_id' => $not_deleted_id
            ]));

        }

        $data = ['status' => 'success'];

        exit(json_encode($data));

    }

}