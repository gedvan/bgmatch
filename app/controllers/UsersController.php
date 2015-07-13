<?php

class UsersController extends ControllerBase
{
    public function loginAction()
    {
        if ($this->request->isPost()) {
            $name = $this->request->getPost('name');
            $user = Users::findFirst(['username = ?1', 'bind' => array(1 => $name)]);
            if ($user) {
                $this->session->loggedUser = $user;
                $this->flashSession->success('Logado como ' . $user->username);
            } else {
                $this->flashSession->error('Usuário não existente');
            }
            $this->response->redirect("/");
        }
    }
}

