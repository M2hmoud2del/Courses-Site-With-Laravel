@extends('layouts.admin')

@section('title', 'Edit User - ' . $user->full_name)

@section('admin-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Edit User</h2>
            <p>Update user information</p>
        </div>
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Users
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" style="max-width: 600px; margin: 0 auto;">
            @csrf
            @method('PUT')
            
            <div class="user-header" style="margin-bottom: 2rem;">
                <div class="user-avatar-placeholder" style="width: 80px; height: 80px; font-size: 2rem;">
                    @php
                        $avatarColors = [
                            'STUDENT' => ['bg' => 'linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%)', 'icon' => 'fa-user-circle'],
                            'INSTRUCTOR' => ['bg' => 'linear-gradient(135deg, #8b5cf6 0%, #ec4899 50%, #f59e0b 100%)', 'icon' => 'fa-chalkboard-teacher'],
                            'ADMIN' => ['bg' => 'linear-gradient(135deg, #ef4444 0%, #f59e0b 50%, #84cc16 100%)', 'icon' => 'fa-user-shield'],
                            'student' => ['bg' => 'linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%)', 'icon' => 'fa-user-circle'],
                            'instructor' => ['bg' => 'linear-gradient(135deg, #8b5cf6 0%, #ec4899 50%, #f59e0b 100%)', 'icon' => 'fa-chalkboard-teacher'],
                            'admin' => ['bg' => 'linear-gradient(135deg, #ef4444 0%, #f59e0b 50%, #84cc16 100%)', 'icon' => 'fa-user-shield'],
                        ];
                        
                        $role = strtoupper($user->role);
                        $avatar = $avatarColors[$role] ?? $avatarColors['STUDENT'];
                    @endphp
                    <i class="fas {{ $avatar['icon'] }}" style="background: {{ $avatar['bg'] }}; -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                </div>
                <div class="user-info">
                    <h3>{{ $user->full_name ?? $user->name }}</h3>
                    <p style="color: var(--gray-600); font-size: 0.875rem;">ID: {{ $user->id }}</p>
                </div>
            </div>
            
            @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                {{ session('success') }}
            </div>
            @endif
            
            @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1rem;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <div class="form-group">
                <label for="name">Username *</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" class="form-input" value="{{ old('full_name', $user->full_name) }}" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
            </div>
            
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" class="form-input" required>
                    <option value="STUDENT" {{ old('role', $user->role) == 'STUDENT' ? 'selected' : '' }}>Student</option>
                    <option value="INSTRUCTOR" {{ old('role', $user->role) == 'INSTRUCTOR' ? 'selected' : '' }}>Instructor</option>
                    <option value="ADMIN" {{ old('role', $user->role) == 'ADMIN' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Account Status</label>
                <div class="toggle-buttons" style="margin-top: 0.5rem;">
                    <button type="button" class="toggle-btn active">Active</button>
                    <button type="button" class="toggle-btn">Inactive</button>
                    <button type="button" class="toggle-btn">Suspended</button>
                    <input type="hidden" id="status" name="status" value="active">
                </div>
            </div>
            
            <div class="form-group">
                <label for="profile_picture">Profile Picture URL</label>
                <input type="text" id="profile_picture" name="profile_picture" class="form-input" value="{{ old('profile_picture', $user->profile_picture) }}" placeholder="Enter image URL">
                @if($user->profile_picture)
                <div style="margin-top: 0.5rem;">
                    <img src="{{ $user->profile_picture }}" alt="Profile Picture" style="max-width: 100px; border-radius: 0.5rem;">
                </div>
                @endif
            </div>
            
            <div class="section">
                <h4 style="margin-bottom: 1rem;">Password Management</h4>
                <div class="form-group">
                    <div class="form-check" style="margin-bottom: 1rem;">
                        <input type="checkbox" id="change_password" class="form-check-input">
                        <label for="change_password" class="form-check-label">Change Password</label>
                    </div>
                </div>
                
                <div id="password_fields" style="display: none;">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Leave blank to keep current password">
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Confirm new password">
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h4 style="margin-bottom: 1rem;">Additional Information</h4>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-input" rows="2">{{ old('address', $user->address) }}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio/Description</label>
                    <textarea id="bio" name="bio" class="form-input" rows="3">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* CSS Variables */
    :root {
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        
        --blue-50: #eff6ff;
        --blue-100: #dbeafe;
        --blue-500: #3b82f6;
        --blue-600: #2563eb;
        
        --green-500: #10b981;
        --green-600: #059669;
        
        --red-500: #ef4444;
        --red-600: #dc2626;
        
        --yellow-500: #f59e0b;
        --yellow-600: #d97706;
    }
    
    /* Page Header */
    .page-header {
        margin-bottom: 2rem;
    }
    
    .page-header h2 {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }
    
    .page-header p {
        color: var(--gray-600);
        font-size: 1rem;
    }
    
    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        text-decoration: none;
        gap: 0.5rem;
    }
    
    .btn-outline {
        background: white;
        border-color: var(--gray-300);
        color: var(--gray-700);
    }
    
    .btn-outline:hover {
        background: var(--gray-50);
        border-color: var(--gray-400);
    }
    
    .btn-primary {
        background: var(--blue-500);
        color: white;
        border-color: var(--blue-500);
    }
    
    .btn-primary:hover {
        background: var(--blue-600);
        border-color: var(--blue-600);
    }
    
    /* Card */
    .card {
        background: white;
        border-radius: 0.75rem;
        border: 1px solid var(--gray-200);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    
    .card-content {
        padding: 1.5rem;
    }
    
    /* User Header */
    .user-header {
        display: flex;
        gap: 1rem;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .user-avatar-placeholder {
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }
    
    .user-info h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    .user-info p {
        margin: 0.25rem 0 0 0;
        color: var(--gray-600);
        font-size: 0.875rem;
    }
    
    /* Alerts */
    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
    }
    
    .alert-success {
        background-color: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    
    .alert-danger {
        background-color: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    
    .alert-danger ul {
        margin: 0;
        padding-left: 1rem;
    }
    
    .alert-danger li {
        margin-bottom: 0.25rem;
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }
    
    .form-input {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: white;
        color: var(--gray-800);
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--blue-500);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    textarea.form-input {
        resize: vertical;
        min-height: 60px;
    }
    
    select.form-input {
        cursor: pointer;
    }
    
    /* Toggle Buttons */
    .toggle-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .toggle-btn {
        padding: 0.5rem 1rem;
        border: 1px solid var(--gray-300);
        background: white;
        border-radius: 0.5rem;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.2s;
        flex: 1;
        color: var(--gray-700);
    }
    
    .toggle-btn:hover {
        border-color: var(--gray-400);
        background: var(--gray-50);
    }
    
    .toggle-btn.active {
        background: var(--blue-600);
        color: white;
        border-color: var(--blue-600);
    }
    
    /* Checkbox */
    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-check-input {
        width: 1rem;
        height: 1rem;
        border-radius: 0.25rem;
        border: 1px solid var(--gray-300);
        cursor: pointer;
    }
    
    .form-check-label {
        font-size: 0.875rem;
        color: var(--gray-700);
        cursor: pointer;
    }
    
    /* Section */
    .section {
        border-top: 1px solid var(--gray-200);
        padding-top: 1.5rem;
        margin-top: 1.5rem;
    }
    
    .section h4 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 1rem;
    }
    
    /* Image Preview */
    img {
        max-width: 100%;
        height: auto;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .user-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .toggle-buttons {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle buttons for status
        const toggleButtons = document.querySelectorAll('.toggle-btn');
        const statusInput = document.getElementById('status');
        
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                toggleButtons.forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update hidden input value
                statusInput.value = this.textContent.toLowerCase().trim();
            });
        });
        
        // Show/hide password fields
        const changePasswordCheckbox = document.getElementById('change_password');
        const passwordFields = document.getElementById('password_fields');
        
        changePasswordCheckbox.addEventListener('change', function() {
            if (this.checked) {
                passwordFields.style.display = 'block';
                document.getElementById('password').required = true;
                document.getElementById('password_confirmation').required = true;
            } else {
                passwordFields.style.display = 'none';
                document.getElementById('password').required = false;
                document.getElementById('password_confirmation').required = false;
                document.getElementById('password').value = '';
                document.getElementById('password_confirmation').value = '';
            }
        });
        
        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            
            if (changePasswordCheckbox.checked && password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (changePasswordCheckbox.checked && password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
        });
    });
</script>
@endpush