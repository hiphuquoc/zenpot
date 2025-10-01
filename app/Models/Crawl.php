<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crawl extends Model {
    use HasFactory;
    protected $table        = 'crawl_info';
    protected $fillable     = [
        'url', 
        'slug_full',
        'title',
        'location',
        'contact_name',
        'contact_phone',
        'content',
        'image_urls',
        'status',
        'notes',
    ];
    public $timestamps      = true;

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new Crawl();
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
