<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserDirectoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:200'], 'sort' => ['nullable', 'in:name,recent']]);
        $query = User::where('is_public', true)->whereNotNull('username')
            ->when($filters['q'] ?? null, fn ($q, $search) => $q->where(fn ($user) => $user->where('name', 'like', '%'.ltrim(trim($search), '@').'%')->orWhere('username', 'like', '%'.ltrim(trim($search), '@').'%')));
        ($filters['sort'] ?? 'name') === 'recent' ? $query->latest() : $query->orderBy('name');
        $users = $query->paginate(24)->withQueryString();

        return view('users.index', compact('users'));
    }
}
