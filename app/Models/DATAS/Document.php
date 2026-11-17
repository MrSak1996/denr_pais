<?php

namespace App\Models\DATAS;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'target_categories';

    protected $fillable = [
        'id',
        'name',
        'order',
        'created_at',
        'updated_at',
    ];
}
