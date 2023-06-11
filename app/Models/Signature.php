<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $table = 'signatures';

    protected $fillable = [
        'user_id',
        'file_id',
        'signature_data',
        'qr_code_data',
        'signature_image',
        'qr_signature',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function file()
{
        return $this->belongsTo(File::class);
    }
}
