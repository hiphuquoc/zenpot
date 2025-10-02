<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTranslate extends Model {
    use HasFactory;
    protected $table        = 'product_info_translate';
    protected $fillable     = [
        'product_info_id',
        'language',
        'material',
        'usage'
    ];
    public $timestamps = false;

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new ProductTranslate();
            foreach($params as $key => $value) {
                if(in_array($key, self::$fillable)) $model->{$key}  = $value;
            }
            $model->save();
            $id         = $model->id;
        }
        return $id;
    }

    public static function updateItem($id, $params){
        $flag           = false;
        if(!empty($id)&&!empty($params)){
            $model      = self::find($id);
            foreach($params as $key => $value) {
                if(in_array($key, self::$fillable)) $model->{$key}  = $value;
            }
            $flag       = $model->update();
        }
        return $flag;
    }
}
