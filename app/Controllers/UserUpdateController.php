<?php

namespace App\Controllers;

use App\{Redirect, Template, Validation};
use App\Services\UsersService;
use App\Models\User;

class UserUpdateController
{
    public function displayAccount()
    {
        if (!isset($_SESSION["userId"])) {
            return new Redirect("/login");
        }
        return new Template("templates/account.twig");
    }

    public function update(): Redirect
    {
        $password = $_POST["password"] ?? "";
        $name = $_POST["name"] ?? "";
        $email = $_POST["email"] ?? "";

        $user = new User($name, $email);
        $validation = new Validation($user);

        $validation->isUserValid();
        if (!empty($_SESSION["errors"])) {
            return new Redirect("/account");
        }

        $validation->isEmailTaken($_SESSION["userId"]);

        $userPassword = new User(null, null, $password);
        $validationPassword = new Validation($userPassword);
        $validationPassword->isPasswordMatchingHash($_SESSION["userId"], "Edit");
        if (!empty($_SESSION["errors"])) {
            return new Redirect("/account");
        }

        (new UsersService())->updateUser($user, $_SESSION["userId"]);
        if (empty($_SESSION["errors"])) {
            $_SESSION["flashMessages"]["update"] = "Successfully changed the username and e-mail!";
        }
        return new Redirect("/account");
    }

    public function updatePassword(): Redirect
    {
        $passwordCurrent = $_POST["passwordCurrent"] ?? "";
        $passwordNew = $_POST["passwordNew"] ?? "";
        $passwordNewRepeated = $_POST["passwordNewRepeated"] ?? "";

        $userNew = new User(null, null, $passwordNew, $passwordNewRepeated);

        $validationNew = new Validation($userNew);
        $validationNew->isUserValid();
        if (!empty($_SESSION["errors"])) {
            return new Redirect("/account");
        }

        $user = new User(null, null, $passwordCurrent);

        $validation = new Validation($user);
        $validation->isPasswordMatchingHash($_SESSION["userId"], "Password");
        if (!empty($_SESSION["errors"])) {
            return new Redirect("/account");
        }

        (new UsersService())->updateUser($userNew, $_SESSION["userId"]);
        if (empty($_SESSION["errors"])) {
            $_SESSION["flashMessages"]["updatePassword"] = "Successfully changed the password!";
        }
        return new Redirect("/account");
    }

    public function delete(): Redirect
    {
        $password = $_POST["passwordForDeletion"] ?? "";
        $user = new User(null, null, $password);

        $validation = new Validation($user);

        $validation->isPasswordMatchingHash($_SESSION["userId"], "Delete");
        if (!empty($_SESSION["errors"])) {
            return new Redirect("/account");
        }

        (new UsersService())->deleteUser($_SESSION["userId"]);
        if (!empty($_SESSION["errors"])) {
            return new Redirect("/account");
        }

        unset($_SESSION["userId"]);
        $_SESSION["flashMessages"]["delete"] = "Successfully deleted the account!";
        return new Redirect("/");
    }
}