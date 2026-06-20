<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index()
    {
        $users = $this->userService->getPaginatedUsers(10);

        return view('admin.users.index', compact('users'));
    }
}
