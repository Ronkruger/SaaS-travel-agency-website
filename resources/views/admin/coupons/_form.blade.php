<div class="form-group">
    <label>Coupon Code <span class="text-danger">*</span></label>
    <input type="text" name="code" class="form-control" placeholder="e.g. SUMMER20"
           style="text-transform:uppercase" maxlength="50"
           {{ isset($edit) ? 'readonly style="background:#f9fafb;text-transform:uppercase"' : '' }}>
    <small class="text-muted">Letters, numbers, dashes and underscores only. Will be auto-uppercased.</small>
</div>
<div class="form-group">
    <label>Description</label>
    <input type="text" name="description" class="form-control" placeholder="Optional note..." maxlength="500">
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <div class="form-group">
        <label>Discount Type <span class="text-danger">*</span></label>
        <select name="type" class="form-control" required>
            <option value="percent">Percentage (%)</option>
            <option value="fixed">Fixed Amount (₱)</option>
        </select>
    </div>
    <div class="form-group">
        <label>Discount Value <span class="text-danger">*</span></label>
        <input type="number" name="value" class="form-control" min="0.01" step="0.01" required placeholder="e.g. 20">
    </div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <div class="form-group">
        <label>Min Spend (₱)</label>
        <input type="number" name="min_spend" class="form-control" min="0" step="0.01" placeholder="0 = no minimum">
    </div>
    <div class="form-group">
        <label>Usage Limit</label>
        <input type="number" name="usage_limit" class="form-control" min="1" placeholder="Leave blank = unlimited">
    </div>
</div>
<div class="form-group">
    <label>Expiry Date</label>
    <input type="date" name="expires_at" class="form-control">
</div>
<div class="form-group" style="display:flex;align-items:center;gap:.5rem">
    <input type="checkbox" name="is_active" id="is_active_{{ isset($edit) ? 'edit' : 'create' }}" value="1" checked>
    <label for="is_active_{{ isset($edit) ? 'edit' : 'create' }}" style="margin:0;font-weight:normal">Active</label>
</div>
