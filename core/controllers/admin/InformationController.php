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

        $data = $this->stringFieldsToInt($data);

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

            if(!empty($new_data['phones'])) {

                foreach ($new_data['phones'] as $info) {

                    $info = $this->filterData($info, [
                        'phone' => ['necessary']
                    ]);

                    if($this->base_model->checkUniqueField('phones', 'phone', $info['phone'])) throw new ApiException($info['phone'] . ' is duplicated');

                    $this->base_model->create('phones', $info);

                }

            }

            if(!empty($new_data['emails'])) {

                foreach ($new_data['emails'] as $info) {

                    $info = $this->filterData($info, [
                        'email' => ['necessary']
                    ]);

                    if($this->base_model->checkUniqueField('emails', 'email', $info['email'])) throw new ApiException($info['email'] . ' is duplicated');

                    $this->base_model->create('emails', $info);

                }

            }

            if(!empty($new_data['addresses'])) {

                foreach ($new_data['addresses'] as $info) {

                    $info = $this->filterData($info, [
                        'address' => ['necessary']
                    ]);

                    if($this->base_model->checkUniqueField('addresses', 'address', $info['address'])) throw new ApiException($info['address'] . ' is duplicated');

                    $this->base_model->create('addresses', $info);

                }

            }

            if(!empty($new_data['social_networks'])) {

                foreach ($new_data['social_networks'] as $info) {

                    $info = $this->filterData($info, [
                       'name' => ['necessary'],
                        'image' => ['necessary'],
                        'link' => ['necessary']
                    ]);

                    $this->base_model->create('social_networks', $info);

                }

            }

            $this->createLog('created a new contact information');

        }

        if(!empty($data['update'])) {

            $update_data = $this->filterData($data['update'], [
                'emails' => ['optional'],
                'phones' => ['optional'],
                'addresses' => ['optional'],
                'social_networks' => ['optional']
            ]);

            if(!empty($update_data['phones'])) {

                foreach ($update_data['phones'] as $info) {

                    $info = $this->filterData($info, [
                        'id' => ['necessary'],
                        'phone' => ['necessary']
                    ]);

                    if($this->base_model->checkUniqueField('phones', 'phone', $info['phone'])) throw new ApiException($info['phone'] . ' is duplicated');

                    $id = $info['id'];

                    unset($info['id']);

                    $this->base_model->update('phones', $id, $info);

                }

            }

            if(!empty($update_data['emails'])) {

                foreach ($update_data['emails'] as $info) {

                    $info = $this->filterData($info, [
                        'id' => ['necessary'],
                        'email' => ['necessary']
                    ]);

                    if($this->base_model->checkUniqueField('emails', 'email', $info['email'])) throw new ApiException($info['email'] . ' is duplicated');

                    $id = $info['id'];

                    unset($info['id']);

                    $this->base_model->update('emails', $id, $info);

                }

            }

            if(!empty($update_data['addresses'])) {

                foreach ($update_data['addresses'] as $info) {

                    $info = $this->filterData($info, [
                        'id' => ['necessary'],
                        'address' => ['necessary']
                    ]);

                    if($this->base_model->checkUniqueField('addresses', 'address', $info['address'])) throw new ApiException($info['address'] . ' is duplicated');

                    $id = $info['id'];

                    unset($info['id']);

                    $this->base_model->update('addresses', $id, $info);

                }

            }

            if(!empty($update_data['social_networks'])) {

                foreach ($update_data['social_networks'] as $info) {

                    $info = $this->filterData($info, [
                        'id' => ['necessary'],
                        'name' => ['optional'],
                        'image' => ['optional'],
                        'link' => ['optional']
                    ]);

                    $id = $info['id'];

                    unset($info['id']);

                    if(empty($info)) continue;

                    $this->base_model->update('social_networks', $id, $info);

                }

            }

            $this->createLog('updated a contact information');

        }

        exit(json_encode(['status' => 'success']));

    }

    private function delete() {

        $this->checkAccess('work_with_contact_information');

        $data = $this->filterData($this->getDeleteData(), [
            'emails' => ['optional'],
            'phones' => ['optional'],
            'addresses' => ['optional'],
            'social_networks' => ['optional']
        ]);

         if(empty($data)) throw new ApiException('The data wasn\'t provided.', 400);

         foreach ($data as $table => $arr_id) $this->base_model->delete($table, $arr_id);

        $this->createLog('removed a contact information');

         exit(json_encode(['status' => 'success']));

    }

}