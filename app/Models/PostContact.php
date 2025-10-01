<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostContact extends Model {
    use HasFactory;
    protected $table        = 'post_contact';
    protected $fillable     = [
        'post_info_id',
        'avatar_file_cloud',
        'name',
        'position',
        'phone',
        'zalo',
        'email',
    ];
    public $timestamps = false;

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new PostContact();
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

    // public function seo() {
    //     return $this->hasOne(\App\Models\Seo::class, 'id', 'seo_id');
    // }
}
