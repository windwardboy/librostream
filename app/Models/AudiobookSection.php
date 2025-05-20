<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudiobookSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'audiobook_id',
        'section_number',
        'title',
        'source_url',
        'duration',
        'librivox_section_id',
    ];

    /**
     * Get the audiobook that the section belongs to.
     */
    public function audiobook()
    {
        return $this->belongsTo(Audiobook::class);
    }
}
