<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'file_name',
        'file_path',
        'file_size',
        'status',
        'sender_id',
        'receiver_id',
    ];

    // Define relationships with other models (if applicable)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function file()
    {
        return $this->belongsToMany(File::class);
    }
}
