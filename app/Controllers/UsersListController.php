<?php

namespace App\Controllers;

use App\{Template, Session};
use App\Services\UsersListService;
use const App\USERS_PER_PAGE;

class UsersListController
{
    private UsersListService $usersListService;

    public function __construct(UsersListService $usersListService)
    {
        $this->usersListService = $usersListService;
    }

    public function showUsersList(array $page): Template
    {
        $page = (isset($page['page']) && (int)$page['page'] > 0) ? (int)$page['page'] : 1;
        $users = $this->usersListService->getUsersList(
            USERS_PER_PAGE,
            $page
        )->getAll();

        Session::addErrors($this->usersListService->getErrors());

        return new Template ('templates/users.twig', [
            'users' => $users,
            'page' => $page,
            'pageCount' => $this->usersListService->getPageCount(),
        ]);
    }
}