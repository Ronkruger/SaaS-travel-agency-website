<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PageBuilderController extends Controller
{
    public function index()
    {
        $sections     = PageSection::where('page', 'home')->orderBy('sort_order')->get();
        $sectionTypes = PageSection::sectionTypes();

        return view('admin.page-builder.index', compact('sections', 'sectionTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_type' => 'required|string|in:' . implode(',', array_keys(PageSection::sectionTypes())),
        ]);

        $maxOrder = PageSection::where('page', 'home')->max('sort_order') ?? -1;

        $section = PageSection::create([
            'page'         => 'home',
            'section_type' => $validated['section_type'],
            'title'        => PageSection::sectionTypes()[$validated['section_type']]['label'],
            'sort_order'   => $maxOrder + 1,
            'is_active'    => true,
            'content'      => $this->defaultContent($validated['section_type']),
            'settings'     => [],
        ]);

        return redirect()->route('admin.page-builder.edit', $section)
            ->with('success', 'Section added! Customize it below.');
    }

    public function edit(PageSection $page_builder)
    {
        $section      = $page_builder;
        $sectionTypes = PageSection::sectionTypes();

        return view('admin.page-builder.edit', compact('section', 'sectionTypes'));
    }

    public function update(Request $request, PageSection $page_builder)
    {
        $section = $page_builder;

        $validated = $request->validate([
            'title'    => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:1000',
            'content'  => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $section->update([
            'title'    => $validated['title'] ?? $section->title,
            'subtitle' => $validated['subtitle'] ?? $section->subtitle,
            'content'  => $validated['content'] ?? $section->content,
            'settings' => $validated['settings'] ?? $section->settings,
        ]);

        return redirect()->route('admin.page-builder.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(PageSection $page_builder)
    {
        $page_builder->delete();

        return redirect()->route('admin.page-builder.index')
            ->with('success', 'Section removed.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:page_sections,id',
        ]);

        foreach ($request->order as $index => $id) {
            PageSection::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function toggle(PageSection $page_builder)
    {
        $page_builder->update(['is_active' => !$page_builder->is_active]);

        return redirect()->route('admin.page-builder.index')
            ->with('success', 'Section ' . ($page_builder->is_active ? 'enabled' : 'disabled') . '.');
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero' => [
                'heading'          => 'Welcome to Our Agency',
                'subheading'       => 'Discover amazing travel experiences with us.',
                'button_text'      => 'Browse Tours',
                'button_link'      => '/tours',
                'button2_text'     => 'Contact Us',
                'button2_link'     => '/contact',
                'background_image' => '',
            ],
            'features' => [
                'items' => [
                    ['icon' => 'fas fa-globe', 'title' => 'Worldwide Tours', 'description' => 'Explore destinations across the globe.'],
                    ['icon' => 'fas fa-shield-alt', 'title' => 'Secure Booking', 'description' => 'Your bookings are safe and guaranteed.'],
                    ['icon' => 'fas fa-headset', 'title' => '24/7 Support', 'description' => 'We are here to help you anytime.'],
                ],
            ],
            'cta' => [
                'heading'     => 'Ready to Start Your Adventure?',
                'subheading'  => 'Book your dream vacation today.',
                'button_text' => 'Get Started',
                'button_link' => '/tours',
                'background_image' => '',
            ],
            'text_block' => [
                'body' => '<p>Add your content here.</p>',
            ],
            'stats' => [
                'items' => [
                    ['number' => '500+', 'label' => 'Happy Travelers'],
                    ['number' => '50+', 'label' => 'Destinations'],
                    ['number' => '100+', 'label' => 'Tour Packages'],
                    ['number' => '24/7', 'label' => 'Support'],
                ],
            ],
            'promo_banner' => [
                'link' => '',
            ],
            'gallery' => [
                'images' => [],
            ],
            default => [],
        };
    }
}
