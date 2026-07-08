<?php

namespace App\Controllers;

class Users extends BaseController
{
    public function list()
    {
        return view('users/list');
    }
}

?>