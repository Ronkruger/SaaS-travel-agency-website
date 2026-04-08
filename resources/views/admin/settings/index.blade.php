@extends('layouts.admin')
@section('title', 'Branding & Logo Settings')

@section('breadcrumb')
    <span>Settings</span> / <span>Branding</span>
@endsection

@section('content')
<div class="page-title">
    <h2>Branding &amp; Logo Settings</h2>
    <p>Upload your company logo and icon — used across the website, emails, and generated documents.</p>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    {{-- Company Identity --}}
    <div class="card mb-4">
        <div class="card-header"><h3><i class="fas fa-building"></i> Company Identity</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control"
                        value="{{ old('company_name', $companyName) }}" maxlength="100">
                </div>
                <div class="form-group">
                    <label>Tagline</label>
                    <input type="text" name="company_tagline" class="form-control"
                        value="{{ old('company_tagline', $tagline) }}" maxlength="200">
                </div>
            </div>
        </div>
    </div>

    {{-- Logo uploads --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;margin-bottom:24px">

        {{-- Light logo --}}
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-image"></i> Primary Logo <span style="font-weight:400;font-size:.8rem;color:var(--gray-500)">(light background)</span></h3></div>
            <div class="card-body">
                <div id="preview-logo" style="min-height:90px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;padding:12px">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo" style="max-height:80px;max-width:100%;object-fit:contain">
                    @else
                        <span style="color:#94a3b8;font-size:.85rem">No logo uploaded</span>
                    @endif
                </div>
                <input type="file" name="logo" id="input-logo" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                    class="form-control" onchange="previewImg(this,'preview-logo')">
                <small style="color:#6b7280">PNG, JPG, SVG or WebP · max 2 MB · recommended: 400×120 px</small>
                @if($logoUrl)
                    <div style="margin-top:10px">
                        <a href="{{ route('admin.settings.delete-logo') }}" onclick="return deleteLogo('logo_path',this)"
                           style="font-size:.8rem;color:#dc2626;text-decoration:none"><i class="fas fa-trash-alt"></i> Remove</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Dark logo --}}
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-moon"></i> Dark Logo <span style="font-weight:400;font-size:.8rem;color:var(--gray-500)">(dark / footer)</span></h3></div>
            <div class="card-body">
                <div id="preview-logo-dark" style="min-height:90px;background:#1e293b;border:2px dashed #334155;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;padding:12px">
                    @if($logoDarkUrl)
                        <img src="{{ $logoDarkUrl }}" alt="Dark Logo" style="max-height:80px;max-width:100%;object-fit:contain">
                    @else
                        <span style="color:#64748b;font-size:.85rem">No dark logo uploaded</span>
                    @endif
                </div>
                <input type="file" name="logo_dark" id="input-logo-dark" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                    class="form-control" onchange="previewImg(this,'preview-logo-dark')">
                <small style="color:#6b7280">Used in footer and dark-background emails · max 2 MB</small>
                @if($logoDarkUrl)
                    <div style="margin-top:10px">
                        <a href="{{ route('admin.settings.delete-logo') }}" onclick="return deleteLogo('logo_dark_path',this)"
                           style="font-size:.8rem;color:#dc2626;text-decoration:none"><i class="fas fa-trash-alt"></i> Remove</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Favicon --}}
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-window-maximize"></i> Favicon / Icon</h3></div>
            <div class="card-body">
                <div id="preview-favicon" style="min-height:90px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;padding:12px">
                    @if($faviconUrl)
                        <img src="{{ $faviconUrl }}" alt="Favicon" style="max-height:64px;max-width:64px;object-fit:contain">
                    @else
                        <span style="color:#94a3b8;font-size:.85rem">No favicon uploaded</span>
                    @endif
                </div>
                <input type="file" name="favicon" id="input-favicon" accept="image/png,image/jpeg,image/svg+xml,image/webp,image/x-icon"
                    class="form-control" onchange="previewImg(this,'preview-favicon')">
                <small style="color:#6b7280">PNG, SVG or ICO · max 512 KB · recommended: 32×32 or 64×64 px</small>
                @if($faviconUrl)
                    <div style="margin-top:10px">
                        <a href="{{ route('admin.settings.delete-logo') }}" onclick="return deleteLogo('favicon_path',this)"
                           style="font-size:.8rem;color:#dc2626;text-decoration:none"><i class="fas fa-trash-alt"></i> Remove</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Preview banner --}}
    <div class="card mb-4">
        <div class="card-header"><h3><i class="fas fa-eye"></i> Live Preview</h3></div>
        <div class="card-body">
            <p style="font-size:.85rem;color:#6b7280;margin:0 0 16px">How the logo will appear in the header and footer:</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div>
                    <div style="font-size:.75rem;font-weight:600;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">HEADER (light)</div>
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:18px 24px;display:flex;align-items:center;gap:12px">
                        <div id="live-logo-light" style="max-height:44px;display:flex;align-items:center">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" style="max-height:44px;max-width:160px;object-fit:contain" alt="Logo">
                            @else
                                <span style="font-size:1.1rem;font-weight:800;color:#0A2D74">DISCOVER GROUP</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    <div style="font-size:.75rem;font-weight:600;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">FOOTER (dark)</div>
                    <div style="background:#0f172a;border-radius:10px;padding:18px 24px;display:flex;align-items:center;gap:12px">
                        <div id="live-logo-dark" style="max-height:44px;display:flex;align-items:center">
                            @if($logoDarkUrl)
                                <img src="{{ $logoDarkUrl }}" style="max-height:44px;max-width:160px;object-fit:contain" alt="Dark Logo">
                            @elseif($logoUrl)
                                <img src="{{ $logoUrl }}" style="max-height:44px;max-width:160px;object-fit:contain" alt="Logo">
                            @else
                                <span style="font-size:1.1rem;font-weight:800;color:#fff">DISCOVER GROUP</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="min-width:180px">
        <i class="fas fa-save"></i> Save Branding Settings
    </button>
</form>

{{-- ── Homepage Customisation ─────────────────────────────────────────── --}}
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" style="margin-top:32px">
    @csrf @method('PUT')

    <div class="card mb-4">
        <div class="card-header"><h3><i class="fas fa-home"></i> Homepage Customisation</h3></div>
        <div class="card-body">
            <p style="font-size:.85rem;color:#6b7280;margin:0 0 20px">
                These settings only affect the customer-facing homepage. They are not visible in the admin panel.
            </p>

            {{-- Promo Banner --}}
            <h4 style="font-size:1rem;font-weight:700;margin-bottom:12px"><i class="fas fa-image"></i> Promotional Banner</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
                <div class="form-group">
                    <label>Banner Image</label>
                    <div id="preview-promo" style="min-height:80px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;padding:10px">
                        @if($promoBannerUrl)
                            <img src="{{ $promoBannerUrl }}" alt="Banner" style="max-height:72px;max-width:100%;object-fit:contain">
                        @else
                            <span style="color:#94a3b8;font-size:.85rem">No banner uploaded</span>
                        @endif
                    </div>
                    <input type="file" name="promo_banner" accept="image/png,image/jpeg,image/webp"
                        class="form-control" onchange="previewImg(this,'preview-promo')">
                    <small style="color:#6b7280">JPG, PNG or WebP · max 4 MB · recommended: 1200×300 px</small>
                    @if($promoBannerUrl)
                        <div style="margin-top:8px">
                            <a href="#" onclick="return deleteLogo('promo_banner_path',this)"
                               style="font-size:.8rem;color:#dc2626;text-decoration:none"><i class="fas fa-trash-alt"></i> Remove</a>
                        </div>
                    @endif
                </div>
                <div class="form-group">
                    <label>Banner Link <small style="color:#6b7280">(optional)</small></label>
                    <input type="url" name="promo_banner_link" class="form-control"
                        value="{{ old('promo_banner_link', $promoBannerLink) }}"
                        placeholder="https://example.com/promo">
                    <small style="color:#6b7280">Where clicking the banner will lead. Leave blank for no link.</small>
                </div>
            </div>

            <hr style="margin:24px 0">

            {{-- Facebook Embed --}}
            <h4 style="font-size:1rem;font-weight:700;margin-bottom:4px"><i class="fab fa-facebook" style="color:#1877f2"></i> Facebook Embed</h4>
            <p style="font-size:.82rem;color:#6b7280;margin:0 0 10px">
                Paste the full Facebook iframe/blockquote embed code (from Facebook's Embedded Posts tool).
                Leave blank to hide this section on the homepage.
            </p>
            <div class="form-group">
                <textarea name="fb_embed_code" class="form-control" rows="5"
                    placeholder='&lt;iframe src="https://www.facebook.com/plugins/post.php?..." ...&gt;&lt;/iframe&gt;'>{{ old('fb_embed_code', $fbEmbedCode) }}</textarea>
            </div>

            <hr style="margin:24px 0">

            {{-- YouTube Embed --}}
            <h4 style="font-size:1rem;font-weight:700;margin-bottom:4px"><i class="fab fa-youtube" style="color:#ff0000"></i> YouTube Video</h4>
            <p style="font-size:.82rem;color:#6b7280;margin:0 0 10px">
                Paste any YouTube URL — watch, shorts, or embed links are all accepted (e.g. <code>https://www.youtube.com/shorts/VIDEO_ID</code> or <code>https://youtu.be/VIDEO_ID</code>).
                Leave blank to hide this section on the homepage.
            </p>
            <div class="form-group">
                <input type="url" name="yt_embed_url" class="form-control"
                    value="{{ old('yt_embed_url', $ytEmbedUrl) }}"
                    placeholder="https://www.youtube.com/shorts/VIDEO_ID">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="min-width:180px">
        <i class="fas fa-save"></i> Save Homepage Settings
    </button>
</form>
<form id="delete-logo-form" action="{{ route('admin.settings.delete-logo') }}" method="POST" style="display:none">
    @csrf @method('DELETE')
    <input type="hidden" name="key" id="delete-logo-key">
</form>
@endsection

@push('scripts')
<script>
function previewImg(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        preview.innerHTML = `<img src="${e.target.result}" style="max-height:80px;max-width:100%;object-fit:contain">`;
        // Also update live previews
        if (input.name === 'logo') {
            document.getElementById('live-logo-light').innerHTML =
                `<img src="${e.target.result}" style="max-height:44px;max-width:160px;object-fit:contain">`;
        }
        if (input.name === 'logo_dark') {
            document.getElementById('live-logo-dark').innerHTML =
                `<img src="${e.target.result}" style="max-height:44px;max-width:160px;object-fit:contain">`;
        }
    };
    reader.readAsDataURL(input.files[0]);
}

function deleteLogo(key, link) {
    if (!confirm('Remove this image?')) return false;
    document.getElementById('delete-logo-key').value = key;
    document.getElementById('delete-logo-form').submit();
    return false;
}
</script>
@endpush
