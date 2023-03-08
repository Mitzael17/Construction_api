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

        if(isset($_GET['check_names'])) $this->checkNames();

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
              'link' => $this->createLinkForImage($dir . $item),
              'isImage' => exif_imagetype(UPLOAD_DIR . $dir . $item) || preg_match('/.svg$/', $item),
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

        if(isset($_FILES['files'])) $this->uploadFiles();

        if(isset($_POST['new_directory'])) $this->createFolders();

    }

    private function delete() {

        $data = $this->getDeleteData();

        if(!isset($data['files'])) throw new ApiException("The request must contain the parameters: files", 400);

        $files = $data['files'];

        $message_for_folders = '';
        $message_for_files = '';

        foreach ($files as $file) {

            if(is_dir(UPLOAD_DIR . $file)) {

                $message_for_folders .= "$file, ";

                $dir = UPLOAD_DIR . $file;

                if($dir === UPLOAD_DIR) continue;

                $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new \RecursiveIteratorIterator($it,
                    \RecursiveIteratorIterator::CHILD_FIRST);

                foreach($files as $f) {
                    if ($f->isDir()){
                        rmdir($f->getRealPath());
                    } else {
                        unlink($f->getRealPath());
                    }
                }
                rmdir($dir);

            }
            elseif(file_exists(UPLOAD_DIR . $file)) {

                $message_for_files .= "$file, ";
                unlink(UPLOAD_DIR . $file);

            }


        }

        if(!empty($message_for_folders)) $this->createLog('Deleted the folders: ' . rtrim($message_for_folders, ', '));
        if(!empty($message_for_files)) $this->createLog('Deleted the files: ' . rtrim($message_for_files, ', '));

        exit(json_encode(['status' => 'success']));

    }

    private function changeFileName($fileName, $dir = '') {

        if(!file_exists(UPLOAD_DIR . $dir . $fileName)) return $fileName;

        return $this->changeFileName(hash('crc32', time() . rand(0, 1000)) . "_$fileName", $dir);

    }

    private function uploadFiles() {

        $files_to_upload = [];
        $not_uploaded = '';
        $dir = $_POST['dir'] ?? '';

        $result = [
            'files' => [],
        ];

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
                'tmp' => $_FILES['files']['tmp_name'][$key],
                'name' => $fileName
            ];

        }

        if(empty($files_to_upload) && !empty($not_uploaded)) throw new ApiException('The file(s) can\'t be upload. Rename it or turn on \'auto rename mode\'');

        $log = 'Uploaded new files: ';

        foreach ($files_to_upload as $file) {

            move_uploaded_file($file['tmp'], $file['to']);

            $result['files'][] = [
                'name' => $file['name'],
                'link' => $this->createLinkForImage($dir . $file['name']),
                'isImage' => exif_imagetype(UPLOAD_DIR . $dir . $file['name']) || preg_match('/.svg$/', $file['name'])
            ];

            $log .= $file['to'] . ', ';

        }

        $this->createLog(rtrim($log, ', '));

        if(!empty($not_uploaded)) {

            $not_uploaded = addslashes(rtrim($not_uploaded, ', '));

            $result = [
                'status' => 'warning',
                'message' => "The files can't be uploaded (rename it or turn on 'auto rename mode'): $not_uploaded",
                'files' => $result['files']
            ];

            exit(json_encode($result));

        }

        $result['status'] = 'success';

        exit(json_encode($result));

    }

    private function createFolders() {

        if(is_dir(UPLOAD_DIR . $_POST['new_directory'])) throw new ApiException('The directory already exists');

        mkdir(UPLOAD_DIR . $_POST['new_directory']);

        $this->createLog('Created a new directory (' . $_POST['new_directory'] . ')');

        exit(json_encode(['status' => 'success']));

    }

    private function checkNames() {

        $files = $_GET['check_names'];
        $dir = $_GET['dir'] ?? '';

        $busy_names = [];

        foreach ($files as $file) {

            if(!file_exists(UPLOAD_DIR . $dir . $file)) continue;

            $busy_names[] = $file;

        }

        if(!empty($busy_names)) {

            exit(json_encode(['status' => 'warning', 'busy_names' => $busy_names]));

        }

        exit(json_encode(['status' => 'success']));

    }

}