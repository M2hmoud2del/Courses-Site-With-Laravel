@extends('layouts.admin')

@section('title', 'Categories Management')

@section('admin-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Categories Management</h2>
            <p>Manage all course categories</p>
        </div>
        <button class="btn btn-primary" onclick="openAddCategoryModal()">
            <i class="fas fa-plus-circle"></i>
            Add Category
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3>All Categories</h3>
            <p>Total: {{ $categories->count() }} categories</p>
        </div>
    </div>
    
    <div class="card-content">
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif
        <!-- Search Bar -->
        <div class="search-bar">
            <div class="input-with-icon">
                <i class="fas fa-search"></i>
                <input type="text" id="searchCategories" placeholder="Search categories...">
            </div>
        </div>

        <!-- Categories Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Courses Count</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTable">
                    @foreach($categories as $category)
                    <tr data-category-id="{{ $category->id }}" 
                        data-category-name="{{ $category->name }}" 
                        data-category-description="{{ $category->description ?? '' }}">
                        <td style="font-weight: 500;">{{ $category->name }}</td>
                        <td>{{ $category->description ?? 'No description' }}</td>
                        <td>{{ $category->courses_count ?? 0 }}</td>
                        <td>{{ $category->created_at->format('M d, Y') }}</td>
                        <td class="text-right">
                            <div class="table-actions">
                                <button class="icon-btn" onclick="editCategory(this)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.categories.delete', $category->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn" title="Delete">
                                        <i class="fas fa-trash" style="color: #ef4444;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeAddCategoryModal()"></div>
    <div class="modal-content">
        <button class="modal-close" onclick="closeAddCategoryModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="modal-header">
            <h2>Add New Category</h2>
            <p>Create a new course category</p>
        </div>
        
        <div class="modal-body">
            <form id="addCategoryForm" action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="category-name">Category Name *</label>
                    <input type="text" id="category-name" name="name" class="form-input" placeholder="Enter category name" required>
                </div>
                
                <div class="form-group">
                    <label for="category-description">Description</label>
                    <textarea id="category-description" name="description" class="form-input" rows="3" placeholder="Enter category description (optional)"></textarea>
                </div>
            </form>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeAddCategoryModal()">Cancel</button>
            <button type="submit" form="addCategoryForm" class="btn btn-primary">Create Category</button>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeEditCategoryModal()"></div>
    <div class="modal-content">
        <button class="modal-close" onclick="closeEditCategoryModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="modal-header">
            <h2>Edit Category</h2>
            <p>Update category information</p>
        </div>
        
        <div class="modal-body">
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit-category-name">Category Name *</label>
                    <input type="text" id="edit-category-name" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-category-description">Description</label>
                    <textarea id="edit-category-description" name="description" class="form-input" rows="3"></textarea>
                </div>
            </form>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeEditCategoryModal()">Cancel</button>
            <button type="submit" form="editCategoryForm" class="btn btn-primary">Update Category</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }
    
    .data-table thead {
        background: var(--gray-50);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .data-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-700);
        border-bottom: 2px solid var(--gray-300);
        background: var(--gray-50);
    }
    
    .data-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--gray-200);
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    
    .data-table tr:hover {
        background: var(--gray-50);
    }
    
    .table-responsive {
        overflow-x: auto;
        margin-bottom: 1.5rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .table-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    
    .icon-btn {
        background: transparent;
        border: 1px solid var(--gray-300);
        width: 2rem;
        height: 2rem;
        border-radius: 0.375rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-600);
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .icon-btn:hover {
        background: var(--gray-100);
        border-color: var(--gray-400);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .text-right {
        text-align: right;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 0.5rem;
    }
    
    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--gray-300);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: white;
        color: var(--gray-800);
    }
    
    textarea.form-input {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--blue-500);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        background: white;
    }
    
    .form-input::placeholder {
        color: var(--gray-400);
    }
    
    .form-input:disabled {
        background: var(--gray-100);
        border-color: var(--gray-200);
        color: var(--gray-500);
        cursor: not-allowed;
    }
    
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        border-radius: 0.75rem;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .modal-header {
        padding: 1.5rem 1.5rem 0.5rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
    }
    
    .modal-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }
    
    .modal-header p {
        color: var(--gray-600);
        margin: 0.25rem 0 0 0;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        padding: 1rem 1.5rem 1.5rem 1.5rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
    
    .modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: transparent;
        border: none;
        color: var(--gray-500);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: all 0.2s;
    }
    
    .modal-close:hover {
        background: var(--gray-100);
        color: var(--gray-700);
    }
    .alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
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

    .alert i {
        font-size: 1rem;
    }

    .alert-success i {
        color: #059669;
    }

    .alert-danger i {
        color: #dc2626;
    }
</style>
@endpush

@push('scripts')
<script>
    // Modal functions
    function openAddCategoryModal() {
        document.getElementById('addCategoryModal').style.display = 'flex';
    }
    
    function closeAddCategoryModal() {
        document.getElementById('addCategoryModal').style.display = 'none';
        document.getElementById('addCategoryForm').reset();
    }
    
    function editCategory(button) {
        // Get the parent row
        const row = button.closest('tr');
        const categoryId = row.getAttribute('data-category-id');
        const categoryName = row.getAttribute('data-category-name');
        const categoryDescription = row.getAttribute('data-category-description');
        
        // Get modal elements
        const modal = document.getElementById('editCategoryModal');
        const form = document.getElementById('editCategoryForm');
        const nameInput = document.getElementById('edit-category-name');
        const descriptionInput = document.getElementById('edit-category-description');
        
        // Set form action
        form.action = `/admin/categories/${categoryId}/update`;
        
        // Populate form fields with current data
        nameInput.value = categoryName;
        descriptionInput.value = categoryDescription;
        
        // Focus on the name field
        nameInput.focus();
        
        // Show modal
        modal.style.display = 'flex';
    }
    
    function closeEditCategoryModal() {
        document.getElementById('editCategoryModal').style.display = 'none';
    }
    
    // Search functionality
    document.getElementById('searchCategories').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#categoriesTable tr');
        
        rows.forEach(row => {
            const name = row.querySelector('td:first-child').textContent.toLowerCase();
            const description = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            
            const matchesSearch = name.includes(searchTerm) || description.includes(searchTerm);
            row.style.display = matchesSearch ? '' : 'none';
        });
    });
    
    // Close modals when clicking on overlay
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            if (document.getElementById('addCategoryModal').style.display === 'flex') {
                closeAddCategoryModal();
            }
            if (document.getElementById('editCategoryModal').style.display === 'flex') {
                closeEditCategoryModal();
            }
        }
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (document.getElementById('addCategoryModal').style.display === 'flex') {
                closeAddCategoryModal();
            }
            if (document.getElementById('editCategoryModal').style.display === 'flex') {
                closeEditCategoryModal();
            }
        }
    });
</script>
@endpush