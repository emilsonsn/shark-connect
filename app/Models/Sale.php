<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'name',
        'cpf',
        'proposal_number',
        'amount',
        'product',
        'bank',
        'commission_percentage',
        'commission_value',
        'payment_status',
        'sale_date',
        'paid_at',
    ];

    // Relacionamento com usuário (quem cadastrou ou vendedor)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
