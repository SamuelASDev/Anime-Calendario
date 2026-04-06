<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updatePublicProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'username' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:users,username,' . $user->id],
            'bio' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['nullable', 'boolean'],
            'show_watching_public' => ['nullable', 'boolean'],
            'show_completed_public' => ['nullable', 'boolean'],
            'show_favorites_public' => ['nullable', 'boolean'],
            'show_top10_public' => ['nullable', 'boolean'],
            'show_reviews_public' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'username' => $data['username'],
            'bio' => $data['bio'] ?? null,
            'is_public' => $request->boolean('is_public'),
            'show_watching_public' => $request->boolean('show_watching_public'),
            'show_completed_public' => $request->boolean('show_completed_public'),
            'show_favorites_public' => $request->boolean('show_favorites_public'),
            'show_top10_public' => $request->boolean('show_top10_public'),
            'show_reviews_public' => $request->boolean('show_reviews_public'),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Perfil público atualizado com sucesso.');
    }

    public function show($username)
    {
        $user = \App\Models\User::where('username', $username)->firstOrFail();

        if (!$user->is_public) {
            abort(403);
        }

        $watching = collect();
        $completed = collect();
        $favorites = collect();
        $top10 = collect();
        $reviews = collect();

        if ($user->show_watching_public) {
            $watching = \App\Models\WatchPlan::with('anime')
                ->where('user_id', $user->id)
                ->where('watch_status', 'assistindo')
                ->get();
        }

        if ($user->show_completed_public) {
            $completed = \App\Models\WatchPlan::with('anime')
                ->where('user_id', $user->id)
                ->where('watch_status', 'concluido')
                ->get();
        }

        if ($user->show_favorites_public) {
            $favorites = \App\Models\UserAnimeMeta::with('anime')
                ->where('user_id', $user->id)
                ->where('is_favorite', true)
                ->get();
        }

        if ($user->show_top10_public) {
            $top10 = \App\Models\UserAnimeMeta::with('anime')
                ->where('user_id', $user->id)
                ->whereNotNull('top_position')
                ->orderBy('top_position')
                ->get();
        }

        if ($user->show_reviews_public) {
            $reviews = \App\Models\UserAnimeMeta::with('anime')
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereNotNull('rating')
                    ->orWhereNotNull('comment');
                })
                ->get();
        }

        return view('profile.show', compact('user', 'watching', 'completed', 'favorites', 'top10', 'reviews'));
    }



}
