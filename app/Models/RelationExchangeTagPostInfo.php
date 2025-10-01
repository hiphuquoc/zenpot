<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationExchangeTagPostInfo extends Model {
    use HasFactory;
    protected $table        = 'relation_exchange_tag_post_info';
    protected $fillable     = [
        'exchange_tag_id', 
        'post_info_id'
    ];
    public $timestamps      = false;

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new RelationExchangeTagPostInfo();
            foreach($params as $key => $value) $model->{$key}  = $value;
            $model->save();
            $id         = $model->id;
        }
        return $id;
    }

    public function infoExchangeTag(){
        return $this->hasOne(\App\Models\ExchangeTag::class, 'id', 'exchange_tag_id');
    }

    public function infoPost(){
        return $this->hasOne(\App\Models\Post::class, 'id', 'post_info_id');
    }
}
