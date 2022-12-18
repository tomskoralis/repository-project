<?php

namespace App\Controllers;

use App\{Template, Session};
use App\Services\UsersSearchService;
use const App\USERS_PER_PAGE;

class UsersSearchController
{
    private UsersSearchService $userSearchService;

    public function __construct(UsersSearchService $userSearchService)
    {
        $this->userSearchService = $userSearchService;
    }

    public function searchUsers(array $request): Template
    {
        $page = ((int)$request['page'] > 0) ? $request['page'] : 1;
        $searchQuery = $request['query'] ?? '';

        $users = $this->userSearchService->getUsersList(
            $searchQuery,
            USERS_PER_PAGE,
            $page
        )->getAll();

        Session::addErrors($this->userSearchService->getErrors());

        return new Template ('templates/users_search.twig', [
            'users' => $users,
            'page' => $page,
            'searchText' => $searchQuery,
        ]);
    }
}