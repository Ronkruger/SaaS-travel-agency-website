@extends('layouts.admin')
@section('title', 'Branding & Logo Settings')

@section('breadcrumb')
    <span>Settings</span> / <span>Branding</span>
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-form')
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
            <h4 style="font-size:1rem;font-weight:700;margin-bottom:4px"><i class="fab fa-facebook" style="color:#1877f2"></i> Facebook Embeds</h4>
            <p style="font-size:.82rem;color:#6b7280;margin:0 0 10px">
                Paste each Facebook iframe/blockquote embed code (from Facebook's Embedded Posts tool). Add multiple to create a carousel.
                Leave empty to hide this section.
            </p>
            <div id="fb-list">
                @forelse($fbEmbedItems as $code)
                <div class="embed-item" style="display:flex;gap:8px;margin-bottom:10px;align-items:flex-start">
                    <textarea name="fb_embed_code[]" class="form-control" rows="4"
                        placeholder='&lt;iframe src="https://www.facebook.com/plugins/post.php?..." ...&gt;&lt;/iframe&gt;'>{{ $code }}</textarea>
                    <button type="button" onclick="this.closest('.embed-item').remove()" class="btn btn-sm btn-danger" title="Remove"><i class="fas fa-times"></i></button>
                </div>
                @empty
                <div class="embed-item" style="display:flex;gap:8px;margin-bottom:10px;align-items:flex-start">
                    <textarea name="fb_embed_code[]" class="form-control" rows="4"
                        placeholder='&lt;iframe src="https://www.facebook.com/plugins/post.php?..." ...&gt;&lt;/iframe&gt;'></textarea>
                    <button type="button" onclick="this.closest('.embed-item').remove()" class="btn btn-sm btn-danger" title="Remove"><i class="fas fa-times"></i></button>
                </div>
                @endforelse
            </div>
            <button type="button" onclick="addEmbedFb()" class="btn btn-sm" style="background:#f1f5f9;border:1px solid #cbd5e1;color:#1e293b">
                <i class="fas fa-plus"></i> Add Facebook Post
            </button>

            <hr style="margin:24px 0">

            {{-- YouTube Embed --}}
            <h4 style="font-size:1rem;font-weight:700;margin-bottom:4px"><i class="fab fa-youtube" style="color:#ff0000"></i> YouTube Videos</h4>
            <p style="font-size:.82rem;color:#6b7280;margin:0 0 10px">
                Paste any YouTube URL — watch, shorts, or embed links are all accepted. Add multiple to create a carousel.
                Leave empty to hide this section.
            </p>
            <div id="yt-list">
                @forelse($ytEmbedItems as $url)
                <div class="embed-item" style="display:flex;gap:8px;margin-bottom:10px;align-items:center">
                    <input type="url" name="yt_embed_url[]" class="form-control" value="{{ $url }}"
                        placeholder="https://www.youtube.com/shorts/VIDEO_ID">
                    <button type="button" onclick="this.closest('.embed-item').remove()" class="btn btn-sm btn-danger" title="Remove"><i class="fas fa-times"></i></button>
                </div>
                @empty
                <div class="embed-item" style="display:flex;gap:8px;margin-bottom:10px;align-items:center">
                    <input type="url" name="yt_embed_url[]" class="form-control"
                        placeholder="https://www.youtube.com/shorts/VIDEO_ID">
                    <button type="button" onclick="this.closest('.embed-item').remove()" class="btn btn-sm btn-danger" title="Remove"><i class="fas fa-times"></i></button>
                </div>
                @endforelse
            </div>
            <button type="button" onclick="addEmbedYt()" class="btn btn-sm" style="background:#f1f5f9;border:1px solid #cbd5e1;color:#1e293b">
                <i class="fas fa-plus"></i> Add YouTube Video
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="min-width:180px">
        <i class="fas fa-save"></i> Save Homepage Settings
    </button>
</form>

{{-- ══════════════════════════════════════════════════════
     PDF BOOKING CONFIRMATION SETTINGS
═══════════════════════════════════════════════════════ --}}
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="card mb-4" style="margin-top:2rem">
        <div class="card-header" style="background:#1e293b;color:#fff">
            <h3 style="color:#fff"><i class="fas fa-file-pdf"></i> PDF Subscription Confirmation Settings</h3>
            <p style="margin:.25rem 0 0;font-size:.85rem;color:#94a3b8">Customize how generated subscription confirmation PDFs look.</p>
        </div>
        <div class="card-body">

            {{-- PDF Logo --}}
            <div class="form-group mb-4">
                <label><strong>PDF Logo</strong> <small style="color:#64748b">(appears in PDF header; separate from website logo)</small></label>
                <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
                    <div id="preview-pdf-logo" style="background:#1e293b;padding:12px;border-radius:8px;min-width:100px;min-height:60px;display:flex;align-items:center;justify-content:center">
                        @if(!empty($pdfLogoUrl))
                            <img src="{{ $pdfLogoUrl }}" alt="PDF Logo" style="max-height:48px;max-width:120px;filter:brightness(0) invert(1)">
                        @else
                            @if($logoUrl ?? false)
                                <img src="{{ $logoUrl }}" alt="Logo" style="max-height:48px;max-width:120px;filter:brightness(0) invert(1)">
                                <div style="font-size:.7rem;color:#94a3b8;text-align:center">Using website logo</div>
                            @else
                                <span style="color:#94a3b8;font-size:.8rem">No logo</span>
                            @endif
                        @endif
                    </div>
                    <div style="flex:1">
                        <input type="file" name="pdf_logo" id="input-pdf-logo"
                               accept="image/png,image/jpeg,image/svg+xml,image/webp"
                               onchange="previewImg('input-pdf-logo','preview-pdf-logo','img')"
                               class="form-control">
                        <small style="color:#64748b">PNG, JPG, SVG, WebP — max 2 MB. Leave blank to use the main website logo.</small>
                        @if(!empty($pdfLogoUrl))
                            <br><button type="button" class="btn btn-sm btn-ghost text-danger mt-1"
                                onclick="deleteLogo('pdf_logo_url')">
                                <i class="fas fa-trash"></i> Remove PDF Logo
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Accent + Header/Footer --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1rem">
                <div class="form-group">
                    <label><strong>Accent Color</strong></label>
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <input type="color" name="pdf_accent_color" value="{{ old('pdf_accent_color', $pdfAccentColor) }}"
                               style="height:38px;width:60px;border:1px solid #e2e8f0;border-radius:.5rem;cursor:pointer">
                        <input type="text" name="pdf_accent_color_text" placeholder="#1e3a8a" readonly
                               value="{{ old('pdf_accent_color', $pdfAccentColor) }}"
                               style="background:#f8fafc;border:1px solid #e2e8f0;padding:.4rem .6rem;border-radius:.5rem;font-family:monospace;font-size:.875rem;flex:1">
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Header Label</strong></label>
                    <input type="text" name="pdf_header_text" class="form-control"
                           value="{{ old('pdf_header_text', $pdfHeaderText) }}" maxlength="100"
                           placeholder="OFFICIAL BOOKING CONFIRMATION">
                </div>
                <div class="form-group">
                    <label><strong>Show Payment History</strong></label>
                    <div style="display:flex;align-items:center;gap:.5rem;height:38px">
                        <input type="checkbox" name="pdf_show_payments" id="pdfShowPayments"
                               value="1" {{ ($pdfShowPayments ?? '1') == '1' ? 'checked' : '' }}
                               style="width:18px;height:18px">
                        <label for="pdfShowPayments" style="margin:0;cursor:pointer;font-weight:400">
                            Include payment records / instalment schedule in PDF
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group mb-4">
                <label><strong>Footer / Disclaimer Text</strong></label>
                <textarea name="pdf_footer_text" class="form-control" rows="2" maxlength="500"
                          placeholder="Thank you for choosing us...">{{ old('pdf_footer_text', $pdfFooterText) }}</textarea>
            </div>

            {{-- Contact details for PDF footer --}}
            <h4 style="font-size:.9rem;color:#475569;margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.05em">PDF Footer Contact Details</h4>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="pdf_contact_email" class="form-control"
                           value="{{ old('pdf_contact_email', $pdfContactEmail) }}" maxlength="200">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="pdf_contact_phone" class="form-control"
                           value="{{ old('pdf_contact_phone', $pdfContactPhone) }}" maxlength="50">
                </div>
                <div class="form-group">
                    <label>Facebook / Website</label>
                    <input type="text" name="pdf_facebook_url" class="form-control"
                           value="{{ old('pdf_facebook_url', $pdfFacebookUrl) }}" maxlength="200">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="pdf_contact_address" class="form-control"
                           value="{{ old('pdf_contact_address', $pdfContactAddr) }}" maxlength="300">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="min-width:200px">
        <i class="fas fa-save"></i> Save PDF Settings
    </button>
</form>

<form id="delete-logo-form" action="{{ route('admin.settings.delete-logo') }}" method="POST" style="display:none">
    @csrf @method('DELETE')
    <input type="hidden" name="key" id="delete-logo-key">
</form>

@push('scripts')
<script>
// Sync color picker <-> text input
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.querySelector('input[name="pdf_accent_color"]');
    const colorText   = document.querySelector('input[name="pdf_accent_color_text"]');
    if (colorPicker && colorText) {
        colorPicker.addEventListener('input', () => { colorText.value = colorPicker.value; });
    }
});
</script>
@endpush

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

function addEmbedFb() {
    var item = document.createElement('div');
    item.className = 'embed-item';
    item.style.cssText = 'display:flex;gap:8px;margin-bottom:10px;align-items:flex-start';
    item.innerHTML = '<textarea name="fb_embed_code[]" class="form-control" rows="4" placeholder=\'<iframe src="https://www.facebook.com/plugins/post.php?..." ...></iframe>\'></textarea>'
        + '<button type="button" onclick="this.closest(\'.embed-item\').remove()" class="btn btn-sm btn-danger" title="Remove"><i class="fas fa-times"></i></button>';
    document.getElementById('fb-list').appendChild(item);
}
function addEmbedYt() {
    var item = document.createElement('div');
    item.className = 'embed-item';
    item.style.cssText = 'display:flex;gap:8px;margin-bottom:10px;align-items:center';
    item.innerHTML = '<input type="url" name="yt_embed_url[]" class="form-control" placeholder="https://www.youtube.com/shorts/VIDEO_ID">'
        + '<button type="button" onclick="this.closest(\'.embed-item\').remove()" class="btn btn-sm btn-danger" title="Remove"><i class="fas fa-times"></i></button>';
    document.getElementById('yt-list').appendChild(item);
}
</script>
@endpush
