<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model {
    use HasFactory;
    protected $table        = 'province_info';
    protected $fillable     = [
        'name',
        'code', 
        'postcode_prefix',
        'merged_from',
        'admin_center',
        'area_km2',
        'population',
        'effective_date',
        'notes',
    ];
    public $timestamps = false;

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new Province();
            foreach($params as $key => $value) $model->{$key}  = $value;
            $model->save();
            $id         = $model->id;
        }
        return $id;
    }

    public static function updateItem($id, $params){
        $flag           = false;
        if(!empty($id)&&!empty($params)){
            $model      = self::find($id);
            foreach($params as $key => $value) $model->{$key}  = $value;
            $flag       = $model->update();
        }
        return $flag;
    }

    public function communes() {
        return $this->hasMany(\App\Models\Commune::class, 'province_info_id', 'id');
    }
}
