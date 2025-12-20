<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <div class="mt-4">
        <div class="flex items-center space-x-4">
            <div class="relative">
                @if($user->profile_picture)
                    <img src="{{ $user->profile_picture }}" 
                         alt="{{ $user->name }}" 
                         class="w-20 h-20 rounded-full object-cover border-2 border-gray-300">
                @else
                    <div class="w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ $user->initial }}
                    </div>
                @endif
                @if($user->profile_picture)
                    <form method="POST" action="{{ route('profile.remove-picture') }}" class="absolute -top-2 -right-2">
                        @csrf
                        <button type="submit" class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors" title="Remove picture">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </form>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-600">Current profile picture</p>
            </div>
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Username')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="full_name" :value="__('Full Name')" />
            <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full" :value="old('full_name', $user->full_name)" autocomplete="name" />
            <p class="text-sm text-gray-500 mt-1">Your complete name (optional)</p>
            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="profile_picture" :value="__('Profile Picture')" />
            <input type="file" id="profile_picture" name="profile_picture" 
                   class="mt-1 block w-full text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-full file:border-0
                          file:text-sm file:font-semibold
                          file:bg-blue-50 file:text-blue-700
                          hover:file:bg-blue-100
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                   accept="image/*">
            <p class="text-sm text-gray-500 mt-1">Upload a new profile picture (max 2MB, jpeg, png, jpg, gif, webp)</p>
            <x-input-error class="mt-2" :messages="$errors->get('profile_picture')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-green-600">
                    {{ __('Profile updated successfully!') }}
                </p>
            @endif

            @if (session('status') === 'profile-picture-removed')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-green-600">
                    {{ __('Profile picture removed successfully!') }}
                </p>
            @endif

            @if (session('error'))
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-red-600">
                    {{ session('error') }}
                </p>
            @endif
        </div>
    </form>
</section>