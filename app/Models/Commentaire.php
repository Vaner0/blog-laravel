<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    
    protected $fillable = [
        'user_id',
        'article_id',
        'contenu',
    ];

    public function auteur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

}
