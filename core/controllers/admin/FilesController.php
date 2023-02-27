<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;
use core\exceptions\RouteException;

class FilesController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->$method($args);

    }

    private function get($args) {

        $dir = $_GET['dir'] ?? '';

        if(!file_exists(UPLOAD_DIR . $dir)) throw new RouteException('The directory doesn\'t exist');

        $files_and_directories = array_diff( scandir(UPLOAD_DIR . $dir, SCANDIR_SORT_NONE), array('..', '.', '.gitignore'));

        $files = [];
        $directories = [];

        foreach ($files_and_directories as $item) {

            if(is_dir(UPLOAD_DIR . $dir . $item)) {

                $directories[] = $item;

                continue;

            }

            $file = [
              'name' => $item,
              'link' => $this->createLinkForImage($dir . $item)
            ];

            $files[] = $file;

        }


        $result = [
            'directories' => $directories,
            'files' => $files
        ];

        exit(json_encode($result));

    }

    private function post() {

        if(isset($_FILES['files'])) {

            $files_to_upload = [];
            $not_uploaded = '';
            $dir = $_POST['dir'] ?? '';

            foreach ($_FILES['files']['name'] as $key => $fileName) {

                if(isset($_POST['auto_rename']) && $_POST['auto_rename']) {

                    $fileName = $this->changeFileName($fileName, $dir);

                }
                elseif(file_exists(UPLOAD_DIR . $dir . $fileName)) {

                    $not_uploaded .= "$fileName, ";
                    continue;

                }

                $files_to_upload[] = [
                    'to' => UPLOAD_DIR . $dir . $fileName,
                    'tmp' => $_FILES['files']['tmp_name'][$key]
                ];

            }

            if(empty($files_to_upload) && !empty($not_uploaded)) throw new ApiException('The file(s) can\'t be upload. Rename it or turn on \'auto rename mode\'');

            foreach ($files_to_upload as $file) move_uploaded_file($file['tmp'], $file['to']);

            if(!empty($not_uploaded)) {

                $not_uploaded = rtrim($not_uploaded, ', ');

                exit(json_encode(['status' => 'warning', 'message' => "The files can't be uploaded (rename it or turn on 'auto rename mode'): $not_uploaded"]));

            }

            exit(json_encode(['status' => 'success']));

        }

    }

    private function delete() {

        $data = $this->getDeleteData();

        $files = $this->filterData($data, [
            'files' => ['necessary']
        ])['files'];

        foreach ($files as $file) {

            if(file_exists(UPLOAD_DIR . $file)) unlink(UPLOAD_DIR . $file);

        }

        exit(json_encode(['status' => 'success']));

    }

    private function changeFileName($fileName, $dir = '') {

        if(!file_exists(UPLOAD_DIR . $dir . $fileName)) return $fileName;

        return $this->changeFileName(hash('crc32', time() . rand(0, 1000)) . "_$fileName", $dir);

    }

}