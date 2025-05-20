<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category; // Import the Category model

class Audiobook extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'narrator',
        'description',
        'cover_image',
        'duration',
        'source_url',
        'category_id',
        'librivox_id',
        'language',
        'librivox_url',
    ];

    /**
     * Get the category that owns the audiobook.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the sections for the audiobook.
     */
    public function sections()
    {
        return $this->hasMany(AudiobookSection::class)->orderBy('section_number');
    }
}
