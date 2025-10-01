<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationPostInfoExchangeOutstanding extends Model {
    use HasFactory;
    protected $table        = 'relation_post_info_exchange_outstanding';
    protected $fillable     = [
        'post_info_id', 
        'exchange_outstanding_id'
    ];
    public $timestamps      = false;

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new RelationPostInfoExchangeOutstanding();
            foreach($params as $key => $value) $model->{$key}  = $value;
            $model->save();
            $id         = $model->id;
        }
        return $id;
    }

    public function infoPost(){
        return $this->hasOne(\App\Models\Post::class, 'id', 'post_info_id');
    }

    public function infoExchangeTag(){
        return $this->hasOne(\App\Models\ExchangeTag::class, 'id', 'exchange_outstanding_id');
    }
}
