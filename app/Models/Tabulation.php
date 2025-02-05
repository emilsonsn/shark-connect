<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tabulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    public function prospects()
    {
        return $this->hasMany(LeadDistributionProspect::class);
    }
}
