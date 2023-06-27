<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /*
    |---------------
    | Relationships
    |---------------
    */

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'detail_transactions')
            ->withPivot(['amount', 'price'])
            ->using(DetailTransaction::class);
    }
}
