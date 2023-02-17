<?php

namespace core\models\admin;

use core\controllers\base\SingleTon;

class LoginModel extends \core\models\base\BaseModel
{

    use SingleTon;

    public function getAdmin($name, $password) {

        $result = $this->query("SELECT * FROM WHERE name='$name' AND password='$password'");

        if(isset($result[0])) return $result[0];

        return $result;

    }

    public function increaseAttempts($ip, $date) {

        $query = "INSERT INTO attempts_entry (ip, attempts, date) VALUES('$ip', 1, '$date') ON DUPLICATE KEY UPDATE attempts=attempts+1";

        $this->query($query, 'u');

    }

    public function getAttempts($ip) {

        $delay = DELAY_ATTEMPTS;

        $result = $this->query("SELECT attempts FROM attempts_entry WHERE ip='$ip' AND DATE_ADD(date, INTERVAL +$delay second ) >= CURRENT_TIMESTAMP");

        if(isset($result[0])) return $result[0]['attempts'];

        $this->clearAttempts($ip);

        return 0;

    }

    public function clearAttempts($ip) {

        $this->query("DELETE FROM attempts_entry WHERE ip='$ip'", 'd');

    }

    public function getAccount($name) {

        $result = $this->query("SELECT id, password FROM admins WHERE name='$name'");

        if(isset($result[0])) return $result[0];

        return false;

    }
}