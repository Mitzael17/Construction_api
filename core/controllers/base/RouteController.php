<?php

namespace core\controllers\base;

use core\exceptions\RouteException;

class RouteController extends BaseController
{

    use SingleTon;

    public function __construct()
    {

        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'],'index.php'));

        if($path !== PATH) throw new RouteException('The directory is not correct', 400);

        $url = $_SERVER['REQUEST_URI'];

        $url = substr($url, strlen(PATH));

        $url = trim($url, '/');

        if(preg_match('/\?/', $url)) $url = substr($url, 0, strpos($url, '?'));

        $urlArr = explode('/', $url);

        if(empty($urlArr[0])) throw new RouteException('The route is invalid. Parameters are empty', 404);

        if($urlArr[0] === ADMIN_PATH) {

            array_shift($urlArr);

            if(empty($urlArr[0])) throw new RouteException('The route is invalid. Parameters are empty', 404);

            $this->controller = ROUTES['admin']['controllers'] . ucfirst($urlArr[0]) . 'Controller';


        } else {

            $this->controller = ROUTES['user']['controllers'] . ucfirst($urlArr[0]) . 'Controller';

        }

        array_shift($urlArr);

        $this->args = $urlArr;



    }

}