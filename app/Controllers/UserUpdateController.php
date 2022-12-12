<?php

namespace App\Controllers;

use App\{Redirect, Session, Template};
use App\Models\User;
use App\Services\UsersService;
use App\Validation\UserValidation;

class UserUpdateController
{
    private UsersService $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }

    public function displayAccount()
    {
        if (!Session::has('userId')) {
            return new Redirect('/login');
        }
        return new Template('templates/account.twig');
    }

    public function update(): Redirect
    {
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';

        $user = new User($name, $email);
        $validation = new UserValidation($user, $this->usersService);

        if (!$validation->isUserValid() || Session::has('errors')) {
            return new Redirect('/account');
        }

        $validation->isEmailTaken(Session::get('userId'));

        $userPassword = new User(null, null, $password);
        $validationPassword = new UserValidation($userPassword, $this->usersService);
        $validationPassword->isPasswordMatchingHash(Session::get('userId'), 'Edit');
        if (Session::has('errors')) {
            return new Redirect('/account');
        }

        $this->usersService->updateUser($user, Session::get('userId'));
        if (!Session::has('errors')) {
            Session::add('Successfully changed the username and e-mail!', 'flashMessages', 'update');
        }
        return new Redirect('/account');
    }

    public function updatePassword(): Redirect
    {
        $passwordCurrent = $_POST['passwordCurrent'] ?? '';
        $passwordNew = $_POST['passwordNew'] ?? '';
        $passwordNewRepeated = $_POST['passwordNewRepeated'] ?? '';

        $userNew = new User(null, null, $passwordNew, $passwordNewRepeated);

        $validationNew = new UserValidation($userNew, $this->usersService);
        if (!$validationNew->isUserValid() || Session::has('errors')) {
            return new Redirect('/account');
        }

        $user = new User(null, null, $passwordCurrent);

        $validation = new UserValidation($user, $this->usersService);
        $validation->isPasswordMatchingHash(Session::get('userId'), 'Password');
        if (Session::has('errors')) {
            return new Redirect('/account');
        }

        $this->usersService->updateUser($userNew, Session::get('userId'));
        if (!Session::has('errors')) {
            Session::add('Successfully changed the password!', 'flashMessages', 'updatePassword');
        }
        return new Redirect('/account');
    }

    public function delete(): Redirect
    {
        $password = $_POST['passwordForDeletion'] ?? '';
        $user = new User(null, null, $password);

        $validation = new UserValidation($user, $this->usersService);

        $validation->isPasswordMatchingHash(Session::get('userId'), 'Delete');
        if (Session::has('errors')) {
            return new Redirect('/account');
        }

        $this->usersService->deleteUser(Session::get('userId'));
        if (Session::has('errors')) {
            return new Redirect('/account');
        }
        Session::remove('userId');
        Session::add('Successfully deleted the account!', 'flashMessages', 'delete');
        return new Redirect('/');
    }
}