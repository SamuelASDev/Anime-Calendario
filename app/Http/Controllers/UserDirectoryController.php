<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserDirectoryController extends Controller
{
    public function __invoke()
    {
        $users = User::where('is_public', true)->whereNotNull('username')->orderBy('name')->paginate(24);

        return view('users.index', compact('users'));
    }
}
