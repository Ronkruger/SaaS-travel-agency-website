<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TourImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'image_path',
        'caption',
        'sort_order',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
