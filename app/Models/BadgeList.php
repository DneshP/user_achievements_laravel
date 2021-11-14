<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BadgeList extends Model
{
    use HasFactory;

    /**
     * Fetch Badge List
     */
    public function badgeList()
    {
        return BadgeList::orderBy('order');
    }
}
