<x-app-layout>
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row gap-6">
            
            <!-- Left Sidebar -->
            <div class="w-full md:w-1/4 space-y-6">
                <!-- Profile Card -->
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="inline-block relative">
                        <!-- Profile Picture Placeholder -->
                         @if($user->profile_picture)
                            <img src="{{ asset('storage/' . $user->profile_picture) }}" class="h-20 w-20 rounded-full mx-auto object-cover" alt="{{ $user->name }}">
                        @else
                            <div class="h-20 w-20 rounded-full mx-auto bg-indigo-500 flex items-center justify-center text-white text-3xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    <a href="{{ route('profile.edit') }}" class="mt-4 inline-block px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Edit Profile
                    </a>
                </div>

                <!-- My Courses Sidebar -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-4">My Courses</h3>
                    @forelse($enrolledCourses as $enrolled)
                        <div class="mb-4 last:mb-0">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 line-clamp-1">{{ $enrolled->title }}</span>
                                <span class="text-gray-500">{{ $enrolled->pivot->progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $enrolled->pivot->progress }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Not enrolled in any courses yet.</p>
                    @endforelse
                    
                    @if($enrolledCourses->count() > 0)
                        <a href="#" class="block text-center text-sm text-blue-600 mt-4 hover:underline">View All</a>
                    @endif
                </div>

                <!-- Notifications Sidebar -->
                 <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-4">Notifications</h3>
                    @forelse($notifications as $notification)
                        <div class="mb-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0 last:mb-0">
                            <p class="text-sm text-gray-700">{{ $notification->message }}</p>
                            <span class="text-xs text-gray-400 block mt-1">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No new notifications.</p>
                    @endforelse
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full md:w-3/4 space-y-6">
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
                    <div class="relative z-10">
                        <h1 class="text-3xl font-bold mb-2">Welcome back, {{ explode(' ', $user->name)[0] }}! 👋</h1>
                        <p class="text-blue-100 max-w-xl">Continue your learning journey and achieve your goals.</p>
                    </div>
                    <!-- Decorative Circles -->
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-blue-400 opacity-20 rounded-full blur-xl"></div>
                </div>

                <!-- Recommended Courses -->
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Recommended Courses</h3>
                    <p class="text-gray-500 mb-6">Discover courses you might like</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($recommendedCourses as $course)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                                <div class="p-5">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $course->category->name ?? 'General' }}
                                        </span>
                                        <div class="flex items-center text-yellow-500 text-sm">
                                            <span>★ 4.9</span> 
                                        </div>
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-1 line-clamp-1">{{ $course->title }}</h4>
                                    <p class="text-sm text-gray-500 mb-3">by {{ $course->instructor->name }}</p>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2 h-10">{{ $course->description }}</p>
                                    
                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                        <span class="text-lg font-bold text-blue-600">${{ $course->price }}</span>
                                        <button class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                            Request to Join
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                                <p class="text-gray-500">No courses available at the moment.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
