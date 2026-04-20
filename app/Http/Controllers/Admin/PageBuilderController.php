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

    public function templates()
    {
        $templates = self::sectionTemplates();
        return view('admin.page-builder.templates', compact('templates'));
    }

    public function applyTemplate(Request $request)
    {
        $validated = $request->validate([
            'template_key' => 'required|string',
        ]);

        $all = self::sectionTemplates();
        $tpl = null;
        foreach ($all as $group) {
            foreach ($group['items'] as $item) {
                if ($item['key'] === $validated['template_key']) { $tpl = $item; break 2; }
            }
        }

        if (!$tpl) {
            return back()->with('error', 'Template not found.');
        }

        $maxOrder = PageSection::where('page', 'home')->max('sort_order') ?? -1;

        $section = PageSection::create([
            'page'         => 'home',
            'section_type' => $tpl['section_type'],
            'title'        => $tpl['content']['heading'] ?? $tpl['content']['title'] ?? $tpl['label'] ?? '',
            'subtitle'     => $tpl['content']['subheading'] ?? $tpl['content']['subtitle'] ?? '',
            'sort_order'   => $maxOrder + 1,
            'is_active'    => true,
            'content'      => $tpl['content'],
            'settings'     => $tpl['settings'] ?? [],
        ]);

        return redirect()->route('admin.page-builder.edit', $section)
            ->with('success', 'Template applied! Customize it below.');
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
            'title'        => '',
            'sort_order'   => $maxOrder + 1,
            'is_active'    => true,
            'content'      => $this->defaultContent($validated['section_type']),
            'settings'     => self::defaultStyles(),
        ]);

        return redirect()->route('admin.page-builder.edit', $section)
            ->with('success', 'Section added! Customize it below.');
    }

    public function edit(PageSection $page_builder)
    {
        $section        = $page_builder;
        $sectionTypes   = PageSection::sectionTypes();
        $availablePages = self::availablePages();

        return view('admin.page-builder.edit', compact('section', 'sectionTypes', 'availablePages'));
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

        $data = [
            'title'    => $validated['title'] ?? $section->title,
            'subtitle' => $validated['subtitle'] ?? $section->subtitle,
            'content'  => $validated['content'] ?? $section->content,
        ];

        // Only update settings if the style panel was opened (inputs enabled & submitted)
        if ($request->has('settings')) {
            $data['settings'] = $validated['settings'];
        }

        $section->update($data);

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

    public static function defaultStyles(): array
    {
        return [
            'bg_color'      => '',
            'text_color'     => '',
            'heading_color'  => '',
            'btn_color'      => '',
            'btn_text_color' => '#ffffff',
            'btn_radius'     => '10',
            'font_size'      => '16',
            'heading_size'   => '',
            'padding_y'      => '60',
        ];
    }

    public static function availablePages(): array
    {
        return [
            ['path' => '/',             'label' => 'Home',          'icon' => 'fas fa-home',          'description' => 'Your main landing page'],
            ['path' => '/tours',        'label' => 'Browse Tours',  'icon' => 'fas fa-map-marked-alt','description' => 'Tour catalog with filters'],
            ['path' => '/about',        'label' => 'About Us',      'icon' => 'fas fa-info-circle',   'description' => 'Company info page'],
            ['path' => '/contact',      'label' => 'Contact',       'icon' => 'fas fa-envelope',      'description' => 'Contact form'],
            ['path' => '/destinations', 'label' => 'Destinations',  'icon' => 'fas fa-globe-americas','description' => 'Destinations by region'],
            ['path' => '/diy',          'label' => 'DIY Builder',   'icon' => 'fas fa-magic',         'description' => 'Custom tour builder'],
            ['path' => '/login',        'label' => 'Login',         'icon' => 'fas fa-sign-in-alt',   'description' => 'Customer login page'],
            ['path' => '/register',     'label' => 'Register',      'icon' => 'fas fa-user-plus',     'description' => 'Customer sign-up page'],
        ];
    }

    public static function sectionTemplates(): array
    {
        return [
            'hero_templates' => [
                'label' => 'Hero Banners',
                'icon'  => 'fas fa-image',
                'items' => [
                    [
                        'key'          => 'hero_travel_dark',
                        'label'        => 'Travel Dark',
                        'section_type' => 'hero',
                        'preview'      => ['bg' => '#0A2D74', 'accent' => '#F5A623', 'style' => 'dark'],
                        'content'      => [
                            'heading'     => 'Explore the World With Us',
                            'subheading'  => 'Premium travel experiences tailored just for you.',
                            'button_text' => 'View Tours', 'button_link' => '/tours',
                            'button2_text' => 'Contact Us', 'button2_link' => '/contact',
                            'background_image' => '',
                        ],
                        'settings' => ['bg_color' => '#0A2D74', 'text_color' => '#ffffff', 'btn_color' => '#F5A623', 'btn_text_color' => '#ffffff', 'btn_radius' => '10', 'heading_size' => '48', 'font_size' => '18', 'padding_y' => '100'],
                    ],
                    [
                        'key'          => 'hero_minimal_light',
                        'label'        => 'Minimal Light',
                        'section_type' => 'hero',
                        'preview'      => ['bg' => '#f8fafc', 'accent' => '#2563eb', 'style' => 'light'],
                        'content'      => [
                            'heading'     => 'Your Next Adventure Starts Here',
                            'subheading'  => 'Browse our curated collection of tours and destinations.',
                            'button_text' => 'Browse Tours', 'button_link' => '/tours',
                            'button2_text' => '', 'button2_link' => '',
                            'background_image' => '',
                        ],
                        'settings' => ['bg_color' => '#f8fafc', 'text_color' => '#1e293b', 'heading_color' => '#0f172a', 'btn_color' => '#2563eb', 'btn_text_color' => '#ffffff', 'btn_radius' => '8', 'heading_size' => '42', 'font_size' => '17', 'padding_y' => '80'],
                    ],
                    [
                        'key'          => 'hero_bold_gradient',
                        'label'        => 'Bold Gradient',
                        'section_type' => 'hero',
                        'preview'      => ['bg' => 'linear-gradient(135deg,#667eea,#764ba2)', 'accent' => '#fbbf24', 'style' => 'dark'],
                        'content'      => [
                            'heading'     => 'Unforgettable Journeys Await',
                            'subheading'  => 'Book your dream vacation today with our expert team.',
                            'button_text' => 'Get Started', 'button_link' => '/tours',
                            'button2_text' => 'Learn More', 'button2_link' => '/about',
                            'background_image' => '',
                        ],
                        'settings' => ['bg_color' => 'linear-gradient(135deg,#667eea,#764ba2)', 'text_color' => '#ffffff', 'btn_color' => '#fbbf24', 'btn_text_color' => '#1e293b', 'btn_radius' => '50', 'heading_size' => '52', 'font_size' => '18', 'padding_y' => '100'],
                    ],
                ],
            ],
            'feature_templates' => [
                'label' => 'Feature Grids',
                'icon'  => 'fas fa-th-large',
                'items' => [
                    [
                        'key'          => 'features_icon_cards',
                        'label'        => 'Icon Cards',
                        'section_type' => 'features',
                        'preview'      => ['bg' => '#ffffff', 'accent' => '#0A2D74', 'style' => 'cards'],
                        'content'      => ['items' => [
                            ['icon' => 'fas fa-globe-americas', 'title' => 'Worldwide', 'description' => 'Tours across every continent.'],
                            ['icon' => 'fas fa-shield-alt', 'title' => 'Secure Booking', 'description' => 'Safe & guaranteed reservations.'],
                            ['icon' => 'fas fa-headset', 'title' => '24/7 Support', 'description' => 'We are always here to help.'],
                        ]],
                        'settings' => ['bg_color' => '#ffffff', 'text_color' => '#374151', 'heading_color' => '#111827', 'btn_color' => '#0A2D74', 'btn_radius' => '14', 'padding_y' => '60'],
                    ],
                    [
                        'key'          => 'features_minimal',
                        'label'        => 'Minimal List',
                        'section_type' => 'features',
                        'preview'      => ['bg' => '#f9fafb', 'accent' => '#10b981', 'style' => 'minimal'],
                        'content'      => ['items' => [
                            ['icon' => 'fas fa-check-circle', 'title' => 'Easy Booking', 'description' => 'Book in just a few clicks.'],
                            ['icon' => 'fas fa-check-circle', 'title' => 'Best Prices', 'description' => 'Competitive rates guaranteed.'],
                            ['icon' => 'fas fa-check-circle', 'title' => 'Expert Guides', 'description' => 'Local guides who know it all.'],
                        ]],
                        'settings' => ['bg_color' => '#f9fafb', 'text_color' => '#374151', 'heading_color' => '#111827', 'btn_color' => '#10b981', 'btn_radius' => '8', 'padding_y' => '50'],
                    ],
                ],
            ],
            'cta_templates' => [
                'label' => 'Call to Action',
                'icon'  => 'fas fa-bullhorn',
                'items' => [
                    [
                        'key'          => 'cta_bold_dark',
                        'label'        => 'Bold Dark',
                        'section_type' => 'cta',
                        'preview'      => ['bg' => '#0f172a', 'accent' => '#F5A623', 'style' => 'dark'],
                        'content'      => ['heading' => 'Ready for Your Next Trip?', 'subheading' => 'Let us plan the perfect getaway.', 'button_text' => 'Book Now', 'button_link' => '/tours', 'background_image' => ''],
                        'settings' => ['bg_color' => '#0f172a', 'text_color' => '#ffffff', 'btn_color' => '#F5A623', 'btn_text_color' => '#ffffff', 'btn_radius' => '10', 'padding_y' => '80'],
                    ],
                    [
                        'key'          => 'cta_soft_blue',
                        'label'        => 'Soft Blue',
                        'section_type' => 'cta',
                        'preview'      => ['bg' => '#eff6ff', 'accent' => '#2563eb', 'style' => 'light'],
                        'content'      => ['heading' => 'Start Your Adventure Today', 'subheading' => 'Hundreds of destinations waiting for you.', 'button_text' => 'Explore Now', 'button_link' => '/tours', 'background_image' => ''],
                        'settings' => ['bg_color' => '#eff6ff', 'text_color' => '#1e3a5f', 'heading_color' => '#1e40af', 'btn_color' => '#2563eb', 'btn_text_color' => '#ffffff', 'btn_radius' => '8', 'padding_y' => '70'],
                    ],
                ],
            ],
            'stats_templates' => [
                'label' => 'Statistics',
                'icon'  => 'fas fa-chart-bar',
                'items' => [
                    [
                        'key'          => 'stats_dark_row',
                        'label'        => 'Dark Row',
                        'section_type' => 'stats',
                        'preview'      => ['bg' => '#0A2D74', 'accent' => '#F5A623', 'style' => 'dark'],
                        'content'      => ['items' => [
                            ['number' => '500+', 'label' => 'Happy Travelers'],
                            ['number' => '50+', 'label' => 'Destinations'],
                            ['number' => '100+', 'label' => 'Tour Packages'],
                            ['number' => '24/7', 'label' => 'Support'],
                        ]],
                        'settings' => ['bg_color' => '#0A2D74', 'text_color' => '#ffffff', 'btn_color' => '#F5A623', 'padding_y' => '48'],
                    ],
                    [
                        'key'          => 'stats_light_cards',
                        'label'        => 'Light Cards',
                        'section_type' => 'stats',
                        'preview'      => ['bg' => '#f9fafb', 'accent' => '#6366f1', 'style' => 'light'],
                        'content'      => ['items' => [
                            ['number' => '1,000+', 'label' => 'Bookings'],
                            ['number' => '98%', 'label' => 'Satisfaction'],
                            ['number' => '50+', 'label' => 'Countries'],
                        ]],
                        'settings' => ['bg_color' => '#f9fafb', 'text_color' => '#374151', 'btn_color' => '#6366f1', 'padding_y' => '60'],
                    ],
                ],
            ],
            'text_templates' => [
                'label' => 'Text & Content',
                'icon'  => 'fas fa-align-left',
                'items' => [
                    [
                        'key'          => 'text_about_centered',
                        'label'        => 'About Us Centered',
                        'section_type' => 'text_block',
                        'preview'      => ['bg' => '#ffffff', 'accent' => '#0A2D74', 'style' => 'text'],
                        'content'      => ['body' => '<p style="font-size:1.05rem;line-height:1.8;color:#374151">We are a passionate travel agency dedicated to creating unforgettable experiences. Our team of experts will help you discover the world\'s most amazing destinations.</p>'],
                        'settings' => ['bg_color' => '#ffffff', 'text_color' => '#374151', 'heading_color' => '#111827', 'padding_y' => '60'],
                    ],
                ],
            ],
        ];
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero' => [
                'heading'          => '',
                'subheading'       => '',
                'button_text'      => '',
                'button_link'      => '',
                'button2_text'     => '',
                'button2_link'     => '',
                'background_image' => '',
            ],
            'features' => [
                'items' => [],
            ],
            'cta' => [
                'heading'     => '',
                'subheading'  => '',
                'button_text' => '',
                'button_link' => '',
                'background_image' => '',
            ],
            'text_block' => [
                'body' => '',
            ],
            'stats' => [
                'items' => [],
            ],
            'promo_banner' => [
                'link' => '',
            ],
            'gallery' => [
                'images' => [],
            ],
            'custom' => [
                'body'             => '',
                'layout'           => 'contained',
                'background_image' => '',
                'button_text'      => '',
                'button_link'      => '',
            ],
            default => [],
        };
    }
}
