<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exchange extends BaseCategory {
    use HasFactory;
    protected $table        = 'exchange_info';
    protected $fillable     = [
        'seo_id',
        'icon',
        'sign',
        'flag_show',
        'notes',
        'updated_at',
    ];
    public $timestamps = true;

    public static function getList($params = null){
        $result     = self::select('*')
                        ->whereHas('seo', function($query){
                            $query->where('language', 'vi');
                        })
                        /* tìm theo tên */
                        ->when(!empty($params['search_name']), function($query) use($params){
                            $query->whereHas('seo', function($subQuery) use($params){
                                $subQuery->where('title', 'like', '%'.$params['search_name'].'%');
                            });
                        })
                        ->orderBy('id', 'DESC')
                        ->with('seo', 'seos')
                        ->paginate($params['paginate']);
        return $result;
    }

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new Exchange();
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

    public static function getArrayIdExchangeRelatedByIdExchange($infoExchange, $variable){
        $idPage             = $infoExchange->seo->id;
        $arrayChild         = self::select('*')
                                ->whereHas('seo', function($query) use($idPage){
                                    $query->where('parent', $idPage);
                                })
                                ->with('seo')
                                ->get();
        /* kiểm tra đã là category cha chưa => chưa thì lấy id category cha gộp vào mảng */
        if(!empty($arrayChild)&&$arrayChild->isNotEmpty()){
            foreach($arrayChild as $child){
                $variable[]     = $child->id;
                self::getArrayIdExchangeRelatedByIdExchange($child, $variable);
            }
        }
        return $variable;
    }

    // quan trọng dùng để tối ưu vòng lặp khi duyệt tìm ngôn ngữ
    public function scopeWithDefaultSeoForLanguage($query, $language) {
        return $query->whereHas('seos', function ($q) use ($language) {
            $q->join('seo', 'seo.id', '=', 'relation_seo_exchange_info.seo_id')
            ->where('seo.language', $language);
        })
        ->with(['seos' => function ($q) use ($language) {
            $q->join('seo', 'seo.id', '=', 'relation_seo_exchange_info.seo_id')
            ->where('seo.language', $language);
        }]);
    }

    public function seo() {
        return $this->hasOne(\App\Models\Seo::class, 'id', 'seo_id');
    }

    public function seos() {
        return $this->hasMany(\App\Models\RelationSeoExchangeInfo::class, 'exchange_info_id', 'id');
    }

    public function tags(){
        return $this->hasMany(\App\Models\RelationExchangeInfoExchangeTag::class, 'exchange_info_id', 'id');
    }

    public function posts(){
        return $this->hasMany(\App\Models\RelationExchangeInfoPostInfo::class, 'exchange_info_id', 'id');
    }
}
