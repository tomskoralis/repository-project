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
                'Need to be logged in to view account!',
                'errors', 'auth'
            );
            return new Redirect('/login');
        }
        return new Template('templates/account.twig');
    }

    public function updateNameAndEmail(): Redirect
    {
        if (!Session::has('userId')) {
            Session::add(
                'Need to be logged in to update account!',
                'errors', 'auth'
            );
            return new Redirect('/login');
        }

        $this->userUpdateService->updateNameAndEmail(
            new User(
                Session::get('userId'),
                $_POST['name'] ?? '',
                $_POST['email'] ?? '',
                $_POST['password'] ?? ''
            )
        );

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
        if (!Session::has('userId')) {
            Session::add(
                'Need to be logged in to update account!',
                'errors', 'auth'
            );
            return new Redirect('/login');
        }

        $this->userUpdateService->updatePassword(
            new User(
                Session::get('userId'),
                null,
                null,
                $_POST['passwordNew'] ?? '',
                $_POST['passwordNewRepeated'] ?? ''
            ),
            $_POST['passwordCurrent'] ?? ''
        );

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

    public function deleteUser(): Redirect
    {
        if (!Session::has('userId')) {
            Session::add(
                'Need to be logged in to update account!',
                'errors', 'auth'
            );
            return new Redirect('/login');
        }

        $this->userDeleteService->deleteUser(
            new User(
                Session::get('userId'),
                null,
                null,
                $_POST['passwordForDeletion'] ?? ''
            )
        );

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