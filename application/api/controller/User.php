<?php

namespace app\api\controller;

class User extends Common
{
    public function login()
    {
        $data = $this->params;
        echo 'welcome to login!';
    }
}
