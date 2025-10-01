<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationExchangeInfoPostInfo extends Model {
    use HasFactory;
    protected $table        = 'relation_exchange_info_post_info';
    protected $fillable     = [
        'exchange_info_id', 
        'post_info_id'
    ];
    public $timestamps      = false;

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new RelationExchangeInfoPostInfo();
            foreach($params as $key => $value) $model->{$key}  = $value;
            $model->save();
            $id         = $model->id;
        }
        return $id;
    }

    public function infoExchangeInfo(){
        return $this->hasOne(\App\Models\Exchange::class, 'id', 'exchange_info_id');
    }

    public function infoPost(){
        return $this->hasOne(\App\Models\Post::class, 'id', 'post_info_id');
    }
}
