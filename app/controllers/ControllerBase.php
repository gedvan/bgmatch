<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
    public function initialize()
    {
        if ($this->session->has('loggedUser')) {
            $this->view->loggedUser = $this->session->loggedUser;
        } else {
            $this->view->loggedUser = NULL;
        }
    }
}
