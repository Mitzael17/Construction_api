<?php

namespace core\controllers\admin;

use core\exceptions\ApiException;

class RouteController extends BaseAdmin
{

    public function inputData($args) {

        $this->init();

        $method = $this->method;

        if(!method_exists($this, $method)) throw new ApiException('The API doesn\'t support the method!', 400);

        $this->$method($args);

    }

    private function get() {

        $routes = [];
        $sidebar = [];

        if($this->user['project_watching']) {

            $routes[] = 'projects';
            $sidebar[] = 'projects';

        }

        if($this->user['project_edit']) {

            $routes[] = 'projects_edit';

        }

        if($this->user['work_with_messages']) {

            $routes[] = 'messages';
            $sidebar[] = 'messages';

        }

        if($this->user['work_with_clients']) {

            $sidebar[] = 'clients';
            $routes[] = 'clients';

        }

        if($this->user['content_page_management'] || $this->user['work_with_news']) {

            $sidebar[] = [
                'name' => 'pages content',
                'values' => []
            ];
            $last_id = count($sidebar) - 1;


            if($this->user['content_page_management']) {

                $sidebar[$last_id]['values'][] = 'layouts';
                $sidebar[$last_id]['values'][] = 'testimonials';
                $sidebar[$last_id]['values'][] =  'partners';

            }

            if($this->user['work_with_news']) {

                $sidebar[$last_id]['values'][] = 'news';
                $routes[] = 'news';

            }

        }

        if($this->user['work_with_contact_information']) {

            $routes[] = 'contacts';
            $sidebar[] = 'contacts';

        }

        if($this->user['service_edit'] || $this->user['work_with_admins'] || $this->user['work_with_news']) {

            $sidebar[]= [
                'name' => 'other data',
                'values' => []
            ];

            $last_id = count($sidebar) - 1;

            if($this->user['work_with_news']) {

                $sidebar[$last_id]['values'][] = 'subscribers';

            }

            if($this->user['service_edit']) {

                $routes[] = 'services';
                $sidebar[$last_id]['values'][] = 'services';

            }

            if($this->user['work_with_admins']) {

                $routes[] = 'admins';
                $routes[] = 'roles';
                $sidebar[$last_id]['values'][] = 'admins';
                $sidebar[$last_id]['values'][] = 'roles';

            }

        }

        $sidebar[] = 'logs';
        $routes[] = 'logs';

        exit(json_encode([
            'sidebar' => $sidebar,
            'routes' => $routes
        ]));

    }

}