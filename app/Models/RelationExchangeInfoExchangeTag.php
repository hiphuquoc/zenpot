<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationExchangeInfoExchangeTag extends Model {
    use HasFactory;
    protected $table        = 'relation_exchange_info_exchange_tag';
    protected $fillable     = [
        'exchange_info_id', 
        'exchange_tag_id'
    ];
    public $timestamps      = false;

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new RelationExchangeInfoExchangeTag();
            foreach($params as $key => $value) $model->{$key}  = $value;
            $model->save();
            $id         = $model->id;
        }
        return $id;
    }

    public function infoExchange(){
        return $this->hasOne(\App\Models\Exchange::class, 'id', 'exchange_info_id');
    }

    public function infoTag(){
        return $this->hasOne(\App\Models\ExchangeTag::class, 'id', 'exchange_tag_id');
    }
}
