<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;

class InformationController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->$method($args);

    }

    private function get() {

        $data = [];

        $data['phones'] = $this->base_model->get('phones');
        $data['addresses'] = $this->base_model->get('addresses');
        $data['emails'] = $this->base_model->get('emails');
        $data['social_networks'] = $this->base_model->get('social_networks');

        exit(json_encode($data));


    }

    private function post($args) {

        $this->checkAccess('work_with_contact_information');

        $data = $this->filterData($_POST, [
            'new' => ['optional'],
            'update' => ['optional']
        ]);

        if(empty($data['new']) && empty($data['update'])) throw new ApiException('The data wasn\'t provided.', 400);

        if(!empty($data['new'])) {

            $new_data = $this->filterData($data['new'], [
               'emails' => ['optional'],
                'phones' => ['optional'],
                'addresses' => ['optional'],
                'social_networks' => ['optional']
            ]);

            foreach ($new_data as $table => $values) $this->base_model->create($table, $values);

        }

        if(!empty($data['update'])) {

            $update_data = $this->filterData($data['update'], [
                'emails' => ['optional'],
                'phones' => ['optional'],
                'addresses' => ['optional'],
                'social_networks' => ['optional']
            ]);

            foreach ($update_data as $table => $values) {

                $id = $values['id'];

                unset($values['id']);

                $this->base_model->update($table, $id, $values);
            }


        }

        exit(json_encode(['status' => 'success']));

    }

    private function delete() {

        $this->checkAccess('work_with_contact_information');

         $data = $this->getDeleteData();

         $data = $this->filterData($data, [
             'emails' => ['optional'],
             'phones' => ['optional'],
             'addresses' => ['optional']
         ]);

         if(empty($data)) throw new ApiException('The data wasn\'t provided.', 400);

         foreach ($data as $table => $arr_id) $this->base_model->delete($table, $arr_id);

         exit(json_encode(['status' => 'success']));

    }

}