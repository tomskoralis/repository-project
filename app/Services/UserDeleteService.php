<?php

namespace App\Services;

use App\Container;
use App\Models\User;
use App\Models\Collections\ErrorsCollection;
use App\Repositories\UsersRepository;
use App\Validation\UserValidation;

class UserDeleteService
{
    private UsersRepository $usersRepository;
    private ErrorsCollection $errors;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
        $this->errors = new ErrorsCollection();
    }

    public function getErrors(): ErrorsCollection
    {
        return $this->errors;
    }

    public function deleteUser(User $user, int $userId): void
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return;
        }

        $validation = Container::get(UserValidation::class);
        /** @var UserValidation $validation */
        if (!$validation->isPasswordMatchingHash($user, $userId, 'Delete')) {
            $this->errors = $validation->getErrors();
            return;
        }

        $this->usersRepository::delete($userId);
    }
}