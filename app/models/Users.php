<?php

use Phalcon\Mvc\Model;

class Users extends Model
{
    public $id;

    public $username;

    public $email;

    public $fb_id;

    public $image;

    public $date_created;
}

