<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    // Define the fillable attributes
    protected $fillable = [
        'original_name',
        'encrypted_name',
        'decrypted_name',
        'unique_file_name',
        'status',
        'sender_id',
        'receiver_id',
        'signature',

    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

}
