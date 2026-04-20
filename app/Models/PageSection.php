<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'content'  => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public static function forPage(string $page = 'home')
    {
        return static::where('page', $page)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public static function sectionTypes(): array
    {
        return [
            'hero' => [
                'label' => 'Hero Banner',
                'icon'  => 'fas fa-image',
                'description' => 'Large hero section with title, subtitle, background image, and call-to-action buttons.',
            ],
            'features' => [
                'label' => 'Features Grid',
                'icon'  => 'fas fa-th-large',
                'description' => 'Grid of feature cards with icons, titles, and descriptions.',
            ],
            'categories' => [
                'label' => 'Tour Categories',
                'icon'  => 'fas fa-tags',
                'description' => 'Displays your tour categories automatically from the database.',
            ],
            'featured_tours' => [
                'label' => 'Featured Tours',
                'icon'  => 'fas fa-star',
                'description' => 'Showcases your featured tours automatically from the database.',
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'icon'  => 'fas fa-quote-right',
                'description' => 'Customer reviews and testimonials from the database.',
            ],
            'cta' => [
                'label' => 'Call to Action',
                'icon'  => 'fas fa-bullhorn',
                'description' => 'Eye-catching banner with a call-to-action button.',
            ],
            'text_block' => [
                'label' => 'Text Block',
                'icon'  => 'fas fa-align-left',
                'description' => 'Free-form text content section with title and body.',
            ],
            'promo_banner' => [
                'label' => 'Promo Banner',
                'icon'  => 'fas fa-ad',
                'description' => 'Full-width promotional image banner.',
            ],
            'stats' => [
                'label' => 'Statistics',
                'icon'  => 'fas fa-chart-bar',
                'description' => 'Animated counter section showing key numbers.',
            ],
            'gallery' => [
                'label' => 'Image Gallery',
                'icon'  => 'fas fa-images',
                'description' => 'Grid of images showcasing destinations or tours.',
            ],
            'custom' => [
                'label' => 'Custom Section',
                'icon'  => 'fas fa-code',
                'description' => 'Freestyle section with custom HTML, layout, and design.',
            ],
        ];
    }
}
