<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListingFiltersRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdatePublicProfileRequest;
use App\Models\User;
use App\Queries\ProfileQuery;
use App\Services\Profiles\ProfileManager;
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

        app(ProfileManager::class)->delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updatePublicProfile(UpdatePublicProfileRequest $request, ProfileManager $profiles)
    {
        $data = $request->validated();
        foreach (['is_public', 'show_watching_public', 'show_completed_public', 'show_favorites_public', 'show_top10_public', 'show_reviews_public'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        $profiles->update($request->user(), $data);

        return redirect()->route('profile.edit')->with('success', 'Perfil atualizado com sucesso.');
    }

    public function show(string $username, ProfileQuery $profiles, ListingFiltersRequest $request)
    {
        $user = User::where('username', $username)->firstOrFail();
        $owner = auth()->id() === $user->id;
        abort_unless($user->is_public || $owner, 403);

        return view('profile.show', $profiles->sections($user, $owner, $request->validated()));
    }
}
