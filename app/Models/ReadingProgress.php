<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingProgress extends Model
{
    protected $table = 'reading_progress';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['position' => 'float', 'furthest_position' => 'float', 'current_page' => 'integer', 'page_count' => 'integer', 'viewed_pages' => 'array', 'saved_at' => 'datetime'];
    }

    public function progressPercent(): int
    {
        return $this->page_count ? (int) floor(count(array_unique($this->viewed_pages ?? [])) / $this->page_count * 100) : (int) floor($this->furthest_position);
    }
}
