{{--
    Link Picker with Page Previews
    Usage: @include('admin.page-builder.partials.link-picker', [
        'name'  => 'content[button_link]',
        'value' => $c['button_link'] ?? '',
        'label' => 'Button 1 Link',
        'pages' => $availablePages,
        'id'    => 'picker-btn1',
    ])
--}}
@php
    $pickerId = $id ?? 'lp-' . md5($name);
    $baseUrl  = rtrim(route('home'), '/');
@endphp
<div class="form-group" style="margin:0;position:relative">
    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">{{ $label }}</label>
    <div style="display:flex;gap:6px;align-items:center">
        <input type="text" name="{{ $name }}" id="{{ $pickerId }}-input" value="{{ old(str_replace(['[',']'], ['.',''], $name), $value) }}" class="form-control" placeholder="/tours" style="flex:1">
        <button type="button" onclick="openLinkPicker('{{ $pickerId }}')" class="btn btn-sm btn-outline" style="white-space:nowrap;padding:8px 12px;flex-shrink:0" title="Browse pages">
            <i class="fas fa-link" style="margin-right:4px"></i> Browse
        </button>
    </div>

    {{-- Dropdown picker --}}
    <div id="{{ $pickerId }}-dropdown" class="link-picker-dropdown" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:1000;margin-top:6px;background:#fff;border:1px solid var(--gray-200);border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.15);max-height:460px;overflow:hidden">
        {{-- Header --}}
        <div style="padding:12px 16px;border-bottom:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between">
            <span style="font-weight:700;font-size:.85rem;color:var(--gray-700)"><i class="fas fa-sitemap" style="margin-right:6px;color:var(--primary)"></i>Select a Page</span>
            <button type="button" onclick="closeLinkPicker('{{ $pickerId }}')" style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:1rem"><i class="fas fa-times"></i></button>
        </div>
        {{-- Page cards --}}
        <div style="padding:12px;overflow-y:auto;max-height:400px;display:grid;grid-template-columns:repeat(2,1fr);gap:10px">
            @foreach($pages as $page)
            <div class="link-picker-card" onclick="selectPage('{{ $pickerId }}', '{{ $page['path'] }}')" style="border:1px solid var(--gray-200);border-radius:10px;overflow:hidden;cursor:pointer;transition:all .2s">
                {{-- Iframe preview --}}
                <div style="position:relative;width:100%;height:110px;overflow:hidden;background:var(--gray-100)">
                    <iframe data-src="{{ $baseUrl }}{{ $page['path'] }}" class="link-picker-iframe" style="width:1280px;height:720px;transform:scale(0.19);transform-origin:top left;border:none;pointer-events:none" loading="lazy" tabindex="-1" sandbox="allow-same-origin"></iframe>
                    {{-- Loading shimmer --}}
                    <div class="link-picker-shimmer" style="position:absolute;inset:0;background:linear-gradient(90deg,var(--gray-100) 25%,var(--gray-50) 50%,var(--gray-100) 75%);background-size:200% 100%;animation:shimmer 1.5s infinite"></div>
                </div>
                {{-- Info --}}
                <div style="padding:10px 12px;display:flex;align-items:center;gap:10px;background:#fff">
                    <div style="width:32px;height:32px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="{{ $page['icon'] }}" style="color:#fff;font-size:.7rem"></i>
                    </div>
                    <div style="min-width:0">
                        <div style="font-weight:700;font-size:.8rem;color:var(--gray-800)">{{ $page['label'] }}</div>
                        <div style="font-size:.7rem;color:var(--gray-400);font-family:monospace">{{ $page['path'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Custom URL card --}}
            <div class="link-picker-card" onclick="closeLinkPicker('{{ $pickerId }}'); document.getElementById('{{ $pickerId }}-input').focus();" style="border:1px dashed var(--gray-300);border-radius:10px;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;min-height:153px">
                <div style="text-align:center;padding:16px">
                    <div style="width:40px;height:40px;background:var(--gray-100);border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px">
                        <i class="fas fa-external-link-alt" style="color:var(--gray-400)"></i>
                    </div>
                    <div style="font-weight:700;font-size:.8rem;color:var(--gray-600)">Custom URL</div>
                    <div style="font-size:.7rem;color:var(--gray-400)">Type any path or external link</div>
                </div>
            </div>
        </div>
    </div>
</div>
