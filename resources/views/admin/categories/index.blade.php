@extends('layouts.admin')
@section('title', 'Categories')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Categories
@endsection

@section('content')
<div class="page-title-row">
    <h2>Tour Categories</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addCatModal').classList.add('open')">
        <i class="fas fa-plus"></i> Add Category
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Icon</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td><strong>{{ $cat->name }}</strong></td>
                        <td><i class="{{ $cat->icon ?? 'fas fa-globe' }} fa-lg"></i></td>
                        <td>
                            @if($cat->is_active)
                                <span class="status-badge status-confirmed">Active</span>
                            @else
                                <span class="status-badge status-cancelled">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn btn-xs btn-outline edit-cat-btn"
                                    data-id="{{ $cat->id }}"
                                    data-name="{{ $cat->name }}"
                                    data-description="{{ $cat->description }}"
                                    data-icon="{{ $cat->icon }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST"
                                      onsubmit="return confirm('Delete category?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No categories.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal" id="addCatModal">
    <div class="modal-backdrop" onclick="this.parentElement.classList.remove('open')"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h4>Add Category</h4>
            <button onclick="document.getElementById('addCatModal').classList.remove('open')">×</button>
        </div>
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group"><label>Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group"><label>Icon class <small>(FontAwesome)</small></label>
                    <input type="text" name="icon" class="form-control" placeholder="fas fa-mountain">
                </div>
                <div class="form-group"><label>Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost"
                    onclick="document.getElementById('addCatModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal" id="editCatModal">
    <div class="modal-backdrop" onclick="this.parentElement.classList.remove('open')"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h4>Edit Category</h4>
            <button onclick="document.getElementById('editCatModal').classList.remove('open')">×</button>
        </div>
        <form id="editCatForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group"><label>Name *</label>
                    <input type="text" name="name" id="editCatName" class="form-control" required>
                </div>
                <div class="form-group"><label>Icon class</label>
                    <input type="text" name="icon" id="editCatIcon" class="form-control">
                </div>
                <div class="form-group"><label>Description</label>
                    <textarea name="description" id="editCatDesc" class="form-control" rows="2"></textarea>
                </div>
                <label class="toggle-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="editCatActive">
                    <span class="toggle-slider"></span> Active
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost"
                    onclick="document.getElementById('editCatModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.edit-cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id  = btn.dataset.id;
        document.getElementById('editCatForm').action = `/admin/categories/${id}`;
        document.getElementById('editCatName').value  = btn.dataset.name;
        document.getElementById('editCatIcon').value  = btn.dataset.icon;
        document.getElementById('editCatDesc').value  = btn.dataset.description;
        document.getElementById('editCatModal').classList.add('open');
    });
});
</script>
@endpush
