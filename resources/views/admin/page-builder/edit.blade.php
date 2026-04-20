@extends('layouts.admin')
@section('title', 'Edit Section')
@section('breadcrumb')
<nav style="font-size:.85rem;color:var(--gray-500)">
    <a href="{{ route('admin.dashboard') }}" style="color:var(--primary)">Dashboard</a>
    <span style="margin:0 6px">/</span>
    <a href="{{ route('admin.page-builder.index') }}" style="color:var(--primary)">Page Builder</a>
    <span style="margin:0 6px">/</span> Edit Section
</nav>
@endsection

@section('content')
@php $meta = \App\Models\PageSection::sectionTypes()[$section->section_type] ?? ['label' => $section->section_type, 'icon' => 'fas fa-puzzle-piece']; @endphp

<div style="display:flex;align-items:center;gap:14px;margin-bottom:24px">
    <a href="{{ route('admin.page-builder.index') }}" class="btn btn-ghost" style="padding:8px 12px">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div style="width:44px;height:44px;background:var(--primary);border-radius:10px;display:flex;align-items:center;justify-content:center">
        <i class="{{ $meta['icon'] }}" style="color:#fff;font-size:1rem"></i>
    </div>
    <div>
        <h1 class="page-title" style="margin:0 0 2px">Edit: {{ $section->title ?: $meta['label'] }}</h1>
        <p style="color:var(--gray-500);font-size:.85rem;margin:0">{{ $meta['description'] ?? '' }}</p>
    </div>
</div>

<form action="{{ route('admin.page-builder.update', $section) }}" method="POST" class="card">
    @csrf @method('PUT')
    <div class="card-body" style="padding:28px">
        {{-- Common fields --}}
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
            <div class="form-group">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Section Title</label>
                <input type="text" name="title" value="{{ old('title', $section->title) }}" class="form-control" placeholder="Section heading">
            </div>
            <div class="form-group">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Subtitle</label>
                <input type="text" name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" class="form-control" placeholder="Optional subtitle">
            </div>
        </div>

        @php $c = $section->content ?? []; @endphp

        {{-- Type-specific fields --}}
        @if($section->section_type === 'hero')
        <div style="border:1px solid var(--gray-200);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--gray-50)">
            <h4 style="margin:0 0 16px;font-size:.9rem;font-weight:700"><i class="fas fa-image" style="margin-right:8px;color:var(--primary)"></i>Hero Content</h4>
            <div class="form-group" style="margin-bottom:14px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Heading</label>
                <input type="text" name="content[heading]" value="{{ old('content.heading', $c['heading'] ?? '') }}" class="form-control" placeholder="Welcome to Our Agency">
            </div>
            <div class="form-group" style="margin-bottom:14px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Subheading</label>
                <textarea name="content[subheading]" class="form-control" rows="2" placeholder="Discover amazing travel experiences...">{{ old('content.subheading', $c['subheading'] ?? '') }}</textarea>
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div class="form-group">
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Button 1 Text</label>
                    <input type="text" name="content[button_text]" value="{{ old('content.button_text', $c['button_text'] ?? '') }}" class="form-control" placeholder="Browse Tours">
                </div>
                <div class="form-group">
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Button 1 Link</label>
                    <input type="text" name="content[button_link]" value="{{ old('content.button_link', $c['button_link'] ?? '') }}" class="form-control" placeholder="/tours">
                </div>
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div class="form-group">
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Button 2 Text</label>
                    <input type="text" name="content[button2_text]" value="{{ old('content.button2_text', $c['button2_text'] ?? '') }}" class="form-control" placeholder="Contact Us">
                </div>
                <div class="form-group">
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Button 2 Link</label>
                    <input type="text" name="content[button2_link]" value="{{ old('content.button2_link', $c['button2_link'] ?? '') }}" class="form-control" placeholder="/contact">
                </div>
            </div>
            <div class="form-group">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Background Image URL</label>
                <input type="text" name="content[background_image]" value="{{ old('content.background_image', $c['background_image'] ?? '') }}" class="form-control" placeholder="https://images.unsplash.com/...">
                <small style="color:var(--gray-500)">Paste an image URL or leave blank for default gradient.</small>
            </div>
        </div>

        @elseif($section->section_type === 'features')
        <div style="border:1px solid var(--gray-200);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--gray-50)">
            <h4 style="margin:0 0 16px;font-size:.9rem;font-weight:700"><i class="fas fa-th-large" style="margin-right:8px;color:var(--primary)"></i>Feature Items</h4>
            <div id="features-list">
                @foreach(($c['items'] ?? []) as $i => $item)
                <div class="feature-item" style="display:grid;grid-template-columns:120px 1fr 1fr auto;gap:10px;margin-bottom:10px;align-items:end">
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Icon Class</label>
                        <input type="text" name="content[items][{{ $i }}][icon]" value="{{ $item['icon'] ?? '' }}" class="form-control" placeholder="fas fa-globe" style="font-size:.85rem">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Title</label>
                        <input type="text" name="content[items][{{ $i }}][title]" value="{{ $item['title'] ?? '' }}" class="form-control" style="font-size:.85rem">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Description</label>
                        <input type="text" name="content[items][{{ $i }}][description]" value="{{ $item['description'] ?? '' }}" class="form-control" style="font-size:.85rem">
                    </div>
                    <button type="button" onclick="this.closest('.feature-item').remove()" class="btn btn-sm btn-ghost" style="color:var(--danger);padding:8px"><i class="fas fa-times"></i></button>
                </div>
                @endforeach
            </div>
            <button type="button" onclick="addFeatureItem()" class="btn btn-sm btn-outline" style="margin-top:8px"><i class="fas fa-plus" style="margin-right:4px"></i> Add Feature</button>
        </div>

        @elseif($section->section_type === 'cta')
        <div style="border:1px solid var(--gray-200);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--gray-50)">
            <h4 style="margin:0 0 16px;font-size:.9rem;font-weight:700"><i class="fas fa-bullhorn" style="margin-right:8px;color:var(--primary)"></i>Call to Action</h4>
            <div class="form-group" style="margin-bottom:14px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Heading</label>
                <input type="text" name="content[heading]" value="{{ old('content.heading', $c['heading'] ?? '') }}" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom:14px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Subheading</label>
                <input type="text" name="content[subheading]" value="{{ old('content.subheading', $c['subheading'] ?? '') }}" class="form-control">
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div class="form-group">
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Button Text</label>
                    <input type="text" name="content[button_text]" value="{{ old('content.button_text', $c['button_text'] ?? '') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Button Link</label>
                    <input type="text" name="content[button_link]" value="{{ old('content.button_link', $c['button_link'] ?? '') }}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Background Image URL</label>
                <input type="text" name="content[background_image]" value="{{ old('content.background_image', $c['background_image'] ?? '') }}" class="form-control">
            </div>
        </div>

        @elseif($section->section_type === 'text_block')
        <div style="border:1px solid var(--gray-200);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--gray-50)">
            <h4 style="margin:0 0 16px;font-size:.9rem;font-weight:700"><i class="fas fa-align-left" style="margin-right:8px;color:var(--primary)"></i>Text Content</h4>
            <div class="form-group">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Body (HTML allowed)</label>
                <textarea name="content[body]" class="form-control" rows="8">{{ old('content.body', $c['body'] ?? '') }}</textarea>
            </div>
        </div>

        @elseif($section->section_type === 'stats')
        <div style="border:1px solid var(--gray-200);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--gray-50)">
            <h4 style="margin:0 0 16px;font-size:.9rem;font-weight:700"><i class="fas fa-chart-bar" style="margin-right:8px;color:var(--primary)"></i>Statistics Items</h4>
            <div id="stats-list">
                @foreach(($c['items'] ?? []) as $i => $item)
                <div class="stat-item" style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;margin-bottom:10px;align-items:end">
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Number</label>
                        <input type="text" name="content[items][{{ $i }}][number]" value="{{ $item['number'] ?? '' }}" class="form-control" style="font-size:.85rem" placeholder="500+">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Label</label>
                        <input type="text" name="content[items][{{ $i }}][label]" value="{{ $item['label'] ?? '' }}" class="form-control" style="font-size:.85rem" placeholder="Happy Travelers">
                    </div>
                    <button type="button" onclick="this.closest('.stat-item').remove()" class="btn btn-sm btn-ghost" style="color:var(--danger);padding:8px"><i class="fas fa-times"></i></button>
                </div>
                @endforeach
            </div>
            <button type="button" onclick="addStatItem()" class="btn btn-sm btn-outline" style="margin-top:8px"><i class="fas fa-plus" style="margin-right:4px"></i> Add Stat</button>
        </div>

        @elseif($section->section_type === 'promo_banner')
        <div style="border:1px solid var(--gray-200);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--gray-50)">
            <h4 style="margin:0 0 16px;font-size:.9rem;font-weight:700"><i class="fas fa-ad" style="margin-right:8px;color:var(--primary)"></i>Promo Banner</h4>
            <div class="form-group" style="margin-bottom:14px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Image URL</label>
                <input type="text" name="content[image_url]" value="{{ old('content.image_url', $c['image_url'] ?? $section->image_url ?? '') }}" class="form-control" placeholder="https://...">
            </div>
            <div class="form-group">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Link (optional)</label>
                <input type="text" name="content[link]" value="{{ old('content.link', $c['link'] ?? '') }}" class="form-control" placeholder="/tours">
            </div>
        </div>

        @elseif(in_array($section->section_type, ['categories', 'featured_tours', 'testimonials']))
        <div style="border:1px solid var(--gray-200);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--gray-50)">
            <h4 style="margin:0 0 8px;font-size:.9rem;font-weight:700"><i class="{{ $meta['icon'] }}" style="margin-right:8px;color:var(--primary)"></i>{{ $meta['label'] }}</h4>
            <p style="color:var(--gray-500);font-size:.85rem;margin:0">
                <i class="fas fa-info-circle" style="margin-right:4px"></i>
                This section automatically pulls data from your database. Just set a title and subtitle above.
            </p>
        </div>

        @elseif($section->section_type === 'gallery')
        <div style="border:1px solid var(--gray-200);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--gray-50)">
            <h4 style="margin:0 0 16px;font-size:.9rem;font-weight:700"><i class="fas fa-images" style="margin-right:8px;color:var(--primary)"></i>Gallery Images</h4>
            <div id="gallery-list">
                @foreach(($c['images'] ?? []) as $i => $img)
                <div class="gallery-item" style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;margin-bottom:10px;align-items:end">
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Image URL</label>
                        <input type="text" name="content[images][{{ $i }}][url]" value="{{ $img['url'] ?? '' }}" class="form-control" style="font-size:.85rem">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Caption</label>
                        <input type="text" name="content[images][{{ $i }}][caption]" value="{{ $img['caption'] ?? '' }}" class="form-control" style="font-size:.85rem">
                    </div>
                    <button type="button" onclick="this.closest('.gallery-item').remove()" class="btn btn-sm btn-ghost" style="color:var(--danger);padding:8px"><i class="fas fa-times"></i></button>
                </div>
                @endforeach
            </div>
            <button type="button" onclick="addGalleryItem()" class="btn btn-sm btn-outline" style="margin-top:8px"><i class="fas fa-plus" style="margin-right:4px"></i> Add Image</button>
        </div>
        @endif

        {{-- Style Customization Panel --}}
        @php $s = $section->settings ?? []; @endphp
        <div style="border:1px solid var(--gray-200);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--gray-50)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;cursor:pointer" onclick="document.getElementById('style-panel').style.display = document.getElementById('style-panel').style.display === 'none' ? 'block' : 'none'; this.querySelector('.chevron').classList.toggle('fa-chevron-down'); this.querySelector('.chevron').classList.toggle('fa-chevron-up')">
                <h4 style="margin:0;font-size:.9rem;font-weight:700"><i class="fas fa-palette" style="margin-right:8px;color:var(--primary)"></i>Style Customization</h4>
                <i class="fas fa-chevron-down chevron" style="color:var(--gray-400);font-size:.75rem"></i>
            </div>
            <div id="style-panel" style="display:none">
                {{-- Colors Row --}}
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;margin-bottom:18px">
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:6px">Background Color</label>
                        <div style="display:flex;gap:6px;align-items:center">
                            <input type="color" name="settings[bg_color]" value="{{ $s['bg_color'] ?? '#ffffff' }}" style="width:36px;height:36px;border:1px solid var(--gray-200);border-radius:8px;cursor:pointer;padding:2px" oninput="this.nextElementSibling.value=this.value">
                            <input type="text" value="{{ $s['bg_color'] ?? '' }}" class="form-control" style="font-size:.8rem" placeholder="#ffffff" oninput="this.previousElementSibling.value=this.value" onchange="this.previousElementSibling.value=this.value; this.form.querySelector('[name=\'settings[bg_color]\']').value=this.value">
                        </div>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:6px">Text Color</label>
                        <div style="display:flex;gap:6px;align-items:center">
                            <input type="color" name="settings[text_color]" value="{{ $s['text_color'] ?? '#374151' }}" style="width:36px;height:36px;border:1px solid var(--gray-200);border-radius:8px;cursor:pointer;padding:2px" oninput="this.nextElementSibling.value=this.value">
                            <input type="text" value="{{ $s['text_color'] ?? '' }}" class="form-control" style="font-size:.8rem" placeholder="#374151" oninput="this.previousElementSibling.value=this.value">
                        </div>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:6px">Heading Color</label>
                        <div style="display:flex;gap:6px;align-items:center">
                            <input type="color" name="settings[heading_color]" value="{{ $s['heading_color'] ?? '#111827' }}" style="width:36px;height:36px;border:1px solid var(--gray-200);border-radius:8px;cursor:pointer;padding:2px" oninput="this.nextElementSibling.value=this.value">
                            <input type="text" value="{{ $s['heading_color'] ?? '' }}" class="form-control" style="font-size:.8rem" placeholder="#111827" oninput="this.previousElementSibling.value=this.value">
                        </div>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:6px">Button Color</label>
                        <div style="display:flex;gap:6px;align-items:center">
                            <input type="color" name="settings[btn_color]" value="{{ $s['btn_color'] ?? '#0A2D74' }}" style="width:36px;height:36px;border:1px solid var(--gray-200);border-radius:8px;cursor:pointer;padding:2px" oninput="this.nextElementSibling.value=this.value">
                            <input type="text" value="{{ $s['btn_color'] ?? '' }}" class="form-control" style="font-size:.8rem" placeholder="#0A2D74" oninput="this.previousElementSibling.value=this.value">
                        </div>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:6px">Button Text Color</label>
                        <div style="display:flex;gap:6px;align-items:center">
                            <input type="color" name="settings[btn_text_color]" value="{{ $s['btn_text_color'] ?? '#ffffff' }}" style="width:36px;height:36px;border:1px solid var(--gray-200);border-radius:8px;cursor:pointer;padding:2px" oninput="this.nextElementSibling.value=this.value">
                            <input type="text" value="{{ $s['btn_text_color'] ?? '' }}" class="form-control" style="font-size:.8rem" placeholder="#ffffff" oninput="this.previousElementSibling.value=this.value">
                        </div>
                    </div>
                </div>

                {{-- Sizing Row --}}
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:18px">
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:6px">Button Radius: <span id="btn-radius-val">{{ $s['btn_radius'] ?? '10' }}px</span></label>
                        <input type="range" name="settings[btn_radius]" min="0" max="50" value="{{ $s['btn_radius'] ?? '10' }}" style="width:100%" oninput="document.getElementById('btn-radius-val').textContent=this.value+'px'">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:6px">Font Size: <span id="font-size-val">{{ $s['font_size'] ?? '16' }}px</span></label>
                        <input type="range" name="settings[font_size]" min="12" max="24" value="{{ $s['font_size'] ?? '16' }}" style="width:100%" oninput="document.getElementById('font-size-val').textContent=this.value+'px'">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:6px">Heading Size: <span id="heading-size-val">{{ $s['heading_size'] ?? '36' }}px</span></label>
                        <input type="range" name="settings[heading_size]" min="20" max="72" value="{{ $s['heading_size'] ?? '36' }}" style="width:100%" oninput="document.getElementById('heading-size-val').textContent=this.value+'px'">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:6px">Section Padding: <span id="padding-val">{{ $s['padding_y'] ?? '60' }}px</span></label>
                        <input type="range" name="settings[padding_y]" min="20" max="120" value="{{ $s['padding_y'] ?? '60' }}" style="width:100%" oninput="document.getElementById('padding-val').textContent=this.value+'px'">
                    </div>
                </div>

                {{-- Live Preview --}}
                <div style="border:1px solid var(--gray-200);border-radius:8px;padding:16px;background:#fff">
                    <label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:8px"><i class="fas fa-eye" style="margin-right:6px;color:var(--primary)"></i>Style Preview</label>
                    <div id="style-preview" style="padding:20px;border-radius:8px;text-align:center;transition:all .3s">
                        <div id="preview-heading" style="font-weight:800;margin-bottom:6px">Heading Text</div>
                        <div id="preview-text" style="margin-bottom:12px">Body text preview</div>
                        <span id="preview-btn" style="display:inline-block;padding:8px 20px;font-weight:700;font-size:.85rem">Button</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check" style="margin-right:6px"></i> Save Changes</button>
            <a href="{{ route('admin.page-builder.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
var featureIdx = {{ count($c['items'] ?? []) }};
function addFeatureItem() {
    var list = document.getElementById('features-list');
    var html = '<div class="feature-item" style="display:grid;grid-template-columns:120px 1fr 1fr auto;gap:10px;margin-bottom:10px;align-items:end">' +
        '<div class="form-group" style="margin:0"><label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Icon Class</label><input type="text" name="content[items]['+featureIdx+'][icon]" class="form-control" placeholder="fas fa-globe" style="font-size:.85rem"></div>' +
        '<div class="form-group" style="margin:0"><label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Title</label><input type="text" name="content[items]['+featureIdx+'][title]" class="form-control" style="font-size:.85rem"></div>' +
        '<div class="form-group" style="margin:0"><label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Description</label><input type="text" name="content[items]['+featureIdx+'][description]" class="form-control" style="font-size:.85rem"></div>' +
        '<button type="button" onclick="this.closest(\'.feature-item\').remove()" class="btn btn-sm btn-ghost" style="color:var(--danger);padding:8px"><i class="fas fa-times"></i></button></div>';
    list.insertAdjacentHTML('beforeend', html);
    featureIdx++;
}

var statIdx = {{ count($c['items'] ?? []) }};
function addStatItem() {
    var list = document.getElementById('stats-list');
    var html = '<div class="stat-item" style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;margin-bottom:10px;align-items:end">' +
        '<div class="form-group" style="margin:0"><label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Number</label><input type="text" name="content[items]['+statIdx+'][number]" class="form-control" style="font-size:.85rem" placeholder="500+"></div>' +
        '<div class="form-group" style="margin:0"><label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Label</label><input type="text" name="content[items]['+statIdx+'][label]" class="form-control" style="font-size:.85rem" placeholder="Happy Travelers"></div>' +
        '<button type="button" onclick="this.closest(\'.stat-item\').remove()" class="btn btn-sm btn-ghost" style="color:var(--danger);padding:8px"><i class="fas fa-times"></i></button></div>';
    list.insertAdjacentHTML('beforeend', html);
    statIdx++;
}

var galleryIdx = {{ count($c['images'] ?? []) }};
function addGalleryItem() {
    var list = document.getElementById('gallery-list');
    var html = '<div class="gallery-item" style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;margin-bottom:10px;align-items:end">' +
        '<div class="form-group" style="margin:0"><label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Image URL</label><input type="text" name="content[images]['+galleryIdx+'][url]" class="form-control" style="font-size:.85rem"></div>' +
        '<div class="form-group" style="margin:0"><label style="display:block;font-weight:600;font-size:.8rem;margin-bottom:4px">Caption</label><input type="text" name="content[images]['+galleryIdx+'][caption]" class="form-control" style="font-size:.85rem"></div>' +
        '<button type="button" onclick="this.closest(\'.gallery-item\').remove()" class="btn btn-sm btn-ghost" style="color:var(--danger);padding:8px"><i class="fas fa-times"></i></button></div>';
    list.insertAdjacentHTML('beforeend', html);
    galleryIdx++;
}

// Live style preview
function updateStylePreview() {
    var bg = document.querySelector('[name="settings[bg_color]"]');
    var text = document.querySelector('[name="settings[text_color]"]');
    var heading = document.querySelector('[name="settings[heading_color]"]');
    var btn = document.querySelector('[name="settings[btn_color]"]');
    var btnText = document.querySelector('[name="settings[btn_text_color]"]');
    var radius = document.querySelector('[name="settings[btn_radius]"]');
    var fontSize = document.querySelector('[name="settings[font_size]"]');
    var headingSize = document.querySelector('[name="settings[heading_size]"]');
    var preview = document.getElementById('style-preview');
    var ph = document.getElementById('preview-heading');
    var pt = document.getElementById('preview-text');
    var pb = document.getElementById('preview-btn');
    if (!preview) return;
    preview.style.background = bg ? bg.value : '#fff';
    pt.style.color = text ? text.value : '#374151';
    pt.style.fontSize = fontSize ? fontSize.value + 'px' : '16px';
    ph.style.color = heading ? heading.value : '#111827';
    ph.style.fontSize = headingSize ? headingSize.value + 'px' : '36px';
    pb.style.background = btn ? btn.value : '#0A2D74';
    pb.style.color = btnText ? btnText.value : '#fff';
    pb.style.borderRadius = radius ? radius.value + 'px' : '10px';
}
document.querySelectorAll('[name^="settings["]').forEach(function(el) {
    el.addEventListener('input', updateStylePreview);
});
setTimeout(updateStylePreview, 100);
</script>
@endpush
