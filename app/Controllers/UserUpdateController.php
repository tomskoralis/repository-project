<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Models\User;
use App\Services\{UserUpdateService, UserDeleteService};

class UserUpdateController
{
    private UserUpdateService $userUpdateService;
    private UserDeleteService $userDeleteService;

    public function __construct(
        UserUpdateService $userUpdateService,
        UserDeleteService $userDeleteService
    )
    {
        $this->userUpdateService = $userUpdateService;
        $this->userDeleteService = $userDeleteService;
    }

    public function showAccount()
    {
        if (!Session::has('userId')) {
            Session::add(
                "Need to be logged in to view account",
                'flashMessages', 'login'
            );
            return new Redirect('/login');
        }
        return new Template('templates/account.twig');
    }

    public function updateNameAndEmail(): Redirect
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = new User($password, $email, $name);

        $this->userUpdateService->updateNameAndEmail($user, Session::get('userId'));
        Session::addErrors($this->userUpdateService->getErrors());
        if (Session::has('errors')) {
            return new Redirect('/account');
        }

        Session::add(
            'Successfully changed the username and e-mail!',
            'flashMessages', 'update'
        );
        return new Redirect('/account');
    }

    public function updatePassword(): Redirect
    {
        $passwordCurrent = $_POST['passwordCurrent'] ?? '';
        $passwordNew = $_POST['passwordNew'] ?? '';
        $passwordNewRepeated = $_POST['passwordNewRepeated'] ?? '';
        $userNew = new User($passwordNew, null, null, $passwordNewRepeated);

        $this->userUpdateService->updatePassword($userNew, $passwordCurrent, Session::get('userId'));
        Session::addErrors($this->userUpdateService->getErrors());
        if (Session::has('errors')) {
            return new Redirect('/account');
        }

        Session::add(
            'Successfully changed the password!',
            'flashMessages', 'updatePassword'
        );
        return new Redirect('/account');
    }

    public function delete(): Redirect
    {
        $password = $_POST['passwordForDeletion'] ?? '';
        $user = new User($password);

        $this->userDeleteService->deleteUser($user, Session::get('userId'));
        Session::addErrors($this->userDeleteService->getErrors());
        if (Session::has('errors')) {
            return new Redirect('/account');
        }

        Session::remove('userId');
        Session::add(
            'Successfully deleted the account!',
            'flashMessages', 'userDeleted'
        );
        return new Redirect('/');
    }
}