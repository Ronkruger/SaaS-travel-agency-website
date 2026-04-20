@extends('layouts.admin')
@section('title', 'Template Gallery')
@section('breadcrumb')
<nav style="font-size:.85rem;color:var(--gray-500)">
    <a href="{{ route('admin.dashboard') }}" style="color:var(--primary)">Dashboard</a>
    <span style="margin:0 6px">/</span>
    <a href="{{ route('admin.page-builder.index') }}" style="color:var(--primary)">Page Builder</a>
    <span style="margin:0 6px">/</span> Templates
</nav>
@endsection

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px">
    <div>
        <h1 class="page-title" style="margin:0 0 4px">Template Gallery</h1>
        <p style="color:var(--gray-500);font-size:.9rem;margin:0">Choose a pre-designed template to add to your page. You can customize everything after.</p>
    </div>
    <a href="{{ route('admin.page-builder.index') }}" class="btn btn-outline" style="gap:6px">
        <i class="fas fa-arrow-left"></i> Back to Builder
    </a>
</div>

@foreach($templates as $groupKey => $group)
<div style="margin-bottom:40px">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
        <div style="width:36px;height:36px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center">
            <i class="{{ $group['icon'] }}" style="color:#fff;font-size:.85rem"></i>
        </div>
        <h2 style="margin:0;font-size:1.1rem;font-weight:700">{{ $group['label'] }}</h2>
        <span style="color:var(--gray-400);font-size:.8rem">({{ count($group['items']) }} templates)</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px">
        @foreach($group['items'] as $tpl)
        @php
            $p = $tpl['preview'] ?? [];
            $bg = $p['bg'] ?? '#f1f5f9';
            $accent = $p['accent'] ?? '#2563eb';
            $isDark = ($p['style'] ?? '') === 'dark';
            $textColor = $isDark ? '#fff' : '#1e293b';
            $subColor = $isDark ? 'rgba(255,255,255,.6)' : '#6b7280';
            $c = $tpl['content'] ?? [];
        @endphp
        <div class="tpl-card" style="border:1px solid var(--gray-200);border-radius:14px;overflow:hidden;transition:all .2s;cursor:pointer" onclick="this.querySelector('form').submit()">
            {{-- Preview --}}
            <div style="height:180px;padding:20px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;{{ str_contains($bg, 'gradient') ? 'background:'.$bg : 'background-color:'.$bg }};position:relative;overflow:hidden">
                @if($tpl['section_type'] === 'hero')
                <div style="font-size:.7rem;font-weight:800;color:{{ $textColor }};margin-bottom:6px;letter-spacing:-.3px;line-height:1.2">{{ Str::limit($c['heading'] ?? 'Heading', 30) }}</div>
                <div style="font-size:.45rem;color:{{ $subColor }};margin-bottom:10px;max-width:180px">{{ Str::limit($c['subheading'] ?? '', 50) }}</div>
                <div style="display:flex;gap:6px">
                    @if(!empty($c['button_text']))
                    <div style="padding:3px 12px;background:{{ $accent }};color:{{ $tpl['settings']['btn_text_color'] ?? '#fff' }};border-radius:{{ min(($tpl['settings']['btn_radius'] ?? 8) / 2, 12) }}px;font-size:.4rem;font-weight:700">{{ $c['button_text'] }}</div>
                    @endif
                    @if(!empty($c['button2_text']))
                    <div style="padding:3px 12px;border:1px solid {{ $isDark ? 'rgba(255,255,255,.4)' : 'rgba(0,0,0,.2)' }};color:{{ $textColor }};border-radius:{{ min(($tpl['settings']['btn_radius'] ?? 8) / 2, 12) }}px;font-size:.4rem;font-weight:700">{{ $c['button2_text'] }}</div>
                    @endif
                </div>

                @elseif($tpl['section_type'] === 'features')
                <div style="display:flex;gap:10px;width:100%">
                    @foreach(array_slice($c['items'] ?? [], 0, 3) as $fi)
                    <div style="flex:1;background:{{ $isDark ? 'rgba(255,255,255,.1)' : '#fff' }};border-radius:8px;padding:10px 6px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.06)">
                        <i class="{{ $fi['icon'] ?? 'fas fa-star' }}" style="color:{{ $accent }};font-size:.6rem;margin-bottom:4px;display:block"></i>
                        <div style="font-size:.38rem;font-weight:700;color:{{ $textColor }}">{{ Str::limit($fi['title'] ?? '', 12) }}</div>
                    </div>
                    @endforeach
                </div>

                @elseif($tpl['section_type'] === 'cta')
                <div style="font-size:.6rem;font-weight:800;color:{{ $textColor }};margin-bottom:6px">{{ Str::limit($c['heading'] ?? '', 35) }}</div>
                <div style="font-size:.4rem;color:{{ $subColor }};margin-bottom:10px">{{ Str::limit($c['subheading'] ?? '', 45) }}</div>
                @if(!empty($c['button_text']))
                <div style="padding:4px 14px;background:{{ $accent }};color:{{ $tpl['settings']['btn_text_color'] ?? '#fff' }};border-radius:{{ min(($tpl['settings']['btn_radius'] ?? 8) / 2, 12) }}px;font-size:.4rem;font-weight:700">{{ $c['button_text'] }}</div>
                @endif

                @elseif($tpl['section_type'] === 'stats')
                <div style="display:flex;gap:16px;width:100%">
                    @foreach(array_slice($c['items'] ?? [], 0, 4) as $si)
                    <div style="flex:1;text-align:center">
                        <div style="font-size:.7rem;font-weight:800;color:{{ $accent }}">{{ $si['number'] ?? '' }}</div>
                        <div style="font-size:.35rem;color:{{ $subColor }}">{{ $si['label'] ?? '' }}</div>
                    </div>
                    @endforeach
                </div>

                @elseif($tpl['section_type'] === 'text_block')
                <div style="width:70%;text-align:left">
                    <div style="height:4px;width:40%;background:{{ $accent }};border-radius:2px;margin-bottom:6px"></div>
                    <div style="height:3px;width:100%;background:{{ $isDark ? 'rgba(255,255,255,.15)' : '#e5e7eb' }};border-radius:1px;margin-bottom:4px"></div>
                    <div style="height:3px;width:90%;background:{{ $isDark ? 'rgba(255,255,255,.15)' : '#e5e7eb' }};border-radius:1px;margin-bottom:4px"></div>
                    <div style="height:3px;width:70%;background:{{ $isDark ? 'rgba(255,255,255,.15)' : '#e5e7eb' }};border-radius:1px"></div>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div style="padding:14px 16px;display:flex;align-items:center;justify-content:space-between;background:#fff;border-top:1px solid var(--gray-100)">
                <div>
                    <div style="font-weight:700;font-size:.85rem;color:var(--gray-800)">{{ $tpl['label'] }}</div>
                    <div style="font-size:.75rem;color:var(--gray-500)">{{ $tpl['section_type'] }}</div>
                </div>
                <form action="{{ route('admin.page-builder.apply-template') }}" method="POST" style="margin:0">
                    @csrf
                    <input type="hidden" name="template_key" value="{{ $tpl['key'] }}">
                    <button type="submit" class="btn btn-sm btn-primary" style="padding:6px 14px;font-size:.8rem">
                        <i class="fas fa-plus" style="margin-right:4px"></i> Use
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
@endsection

@push('styles')
<style>
.tpl-card:hover { border-color: var(--primary); box-shadow: 0 4px 20px rgba(0,0,0,.1); transform: translateY(-2px); }
</style>
@endpush
