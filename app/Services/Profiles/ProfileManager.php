<?php

namespace App\Services\Profiles;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProfileManager
{
    public function update(User $user, array $data): void
    {
        $newFiles = [];
        $oldFiles = [];
        $changedImages = [];
        try {
            foreach (['profile_photo' => 'profiles/photos', 'profile_banner' => 'profiles/banners'] as $key => $directory) {
                if (! empty($data[$key])) {
                    $path = $data[$key]->store($directory, 'public');
                    if (! $path) {
                        throw new RuntimeException('Não foi possível salvar a imagem.');
                    }
                    $newFiles[] = $path;
                    $changedImages[] = $key;
                    $data[$key] = $path;
                } else {
                    unset($data[$key]);
                }
            }
            DB::transaction(function () use ($user, $data, $changedImages, &$oldFiles) {
                $current = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                foreach ($changedImages as $key) {
                    $oldFiles[] = $current->$key;
                }
                $current->update($data);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }
        DB::afterCommit(fn () => Storage::disk('public')->delete(array_values(array_filter($oldFiles))));
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $current = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $files = array_values(array_filter([$current->profile_photo, $current->profile_banner]));
            $current->delete();
            DB::afterCommit(fn () => Storage::disk('public')->delete($files));
        });
    }
}
