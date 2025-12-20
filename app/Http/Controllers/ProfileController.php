<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

 public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();
    
    $user->fill($request->validated());

    if ($request->user()->isDirty('email')) {
        $request->user()->email_verified_at = null;
    }

    $user->full_name = $request->full_name;

    if ($request->hasFile('profile_picture')) {
        // مجلد التخزين
        $folder = 'private/profile-pictures';
        $destination = public_path($folder);
        
        // تأكد من وجود المجلد
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        // حذف جميع الصور القديمة لهذا اليوزر
        $pattern = $destination . '/profile-' . $user->id . '-*';
        $oldFiles = glob($pattern);
        
        foreach ($oldFiles as $oldFile) {
            if (is_file($oldFile)) {
                unlink($oldFile);
            }
        }
        
        // رفع الصورة الجديدة
        $file = $request->file('profile_picture');
        $filename = 'profile-' . $user->id . '-' . time() . '.' . $file->getClientOriginalExtension();
        
        // حفظ الملف
        $file->move($destination, $filename);
        
        // تخزين المسار
        $user->profile_picture = '/' . $folder . '/' . $filename;
    }

    $user->save();

    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}

   public function destroy(Request $request): RedirectResponse
{
    $request->validateWithBag('userDeletion', [
        'password' => ['required', 'current_password'],
    ]);

    $user = $request->user();

    // حذف صورة البروفايل إذا كانت موجودة
    if ($user->profile_picture) {
        $oldPath = ltrim($user->profile_picture, '/');
        $oldFullPath = public_path($oldPath);
        
        if (file_exists($oldFullPath)) {
            unlink($oldFullPath);
        }
    }

    Auth::logout();
    $user->delete();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return Redirect::to('/');
}

public function removeProfilePicture(Request $request): RedirectResponse
{
    $user = $request->user();
    
    if ($user->profile_picture) {
        $oldPath = ltrim($user->profile_picture, '/');
        $oldFullPath = public_path($oldPath);
        
        if (file_exists($oldFullPath)) {
            unlink($oldFullPath);
        }
        
        $user->profile_picture = null;
        $user->save();
        
        return Redirect::route('profile.edit')->with('status', 'profile-picture-removed');
    }
    
    return Redirect::route('profile.edit')->with('error', 'No profile picture to remove.');
}
}