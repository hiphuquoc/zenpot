<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Post extends Model {
    use HasFactory, Searchable;
    protected $table        = 'post_info';
    protected $fillable     = [
        'seo_id', 
        'logo',
        'type_vip',
        'outstanding',
        'status',
        'viewed',
        'notes',
    ];
    public $timestamps      = true;

    /* index dữ liệu SearchData */
    public function toSearchableArray()
    {
        $this->loadMissing(['seo', 'seos.infoSeo', 'exchangeTags.infoExchangeTag.seos.infoSeo']);

        return [
            'id' => $this->id,
            'title' => $this->seo->title ?? '',
            'seos' => $this->seos->pluck('infoSeo.title')->filter()->values()->toArray(),

            // === lấy danh sách title từ các tag ===
            'exchangeTagsTitles' => $this->exchangeTags->flatMap(function ($relation) {
                return $relation->infoExchangeTag->seos
                    ->pluck('infoSeo.title')
                    ->filter()
                    ->values();
            })->unique()->values()->toArray(),
        ];
    }

    public static function getList($params = null){
        if (!empty($params['search_name'])) {
            $searchName = $params['search_name'];
    
            // Lấy danh sách ID từ Meilisearch (tìm trong seo.title)
            $ids = self::search($searchName)->get()->pluck('id')->toArray();
    
            // Truy vấn tiếp tục trong database với điều kiện khác
            $result = self::whereIn('id', $ids)
                        ->when(!empty($params['search_exchange_info']), function($query) use($params){
                            $query->whereHas('exchangeInfos.infoExchangeInfo', function($q) use ($params){
                                $q->where('id', $params['search_exchange_info']);
                            });
                        })
                        ->when(!empty($params['search_exchange_tag']), function($query) use($params){
                            $query->whereHas('exchangeTags.infoExchangeTag', function($q) use ($params){
                                $q->where('id', $params['search_exchange_tag']);
                            });
                        })
                        ->with('seo')
                        ->orderBy('id', 'DESC')
                        ->paginate($params['paginate']);
    
            return $result;
        }

        // Truy vấn mặc định khi không tìm kiếm
        $result     = self::select('*')
                        ->when(!empty($params['search_exchange_info']), function($query) use($params){
                            $query->whereHas('exchangeInfos.infoExchangeInfo', function($q) use ($params){
                                $q->where('id', $params['search_exchange_info']);
                            });
                        })
                        ->when(!empty($params['search_exchange_tag']), function($query) use($params){
                            $query->whereHas('exchangeTags.infoExchangeTag', function($q) use ($params){
                                $q->where('id', $params['search_exchange_tag']);
                            });
                        })
                        ->with('seo')
                        ->orderBy('id', 'DESC')
                        ->paginate($params['paginate']);
        return $result;
    }

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new Post();
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

    // quan trọng dùng để tối ưu vòng lặp khi duyệt tìm ngôn ngữ
    public function scopeWithDefaultSeoForLanguage($query, $language) {
        return $query->whereHas('seos', function ($q) use ($language) {
            $q->join('seo', 'seo.id', '=', 'relation_seo_post_info.seo_id')
            ->where('seo.language', $language);
        })
        ->with(['seos' => function ($q) use ($language) {
            $q->join('seo', 'seo.id', '=', 'relation_seo_post_info.seo_id')
            ->where('seo.language', $language);
        }]);
    }

    public function seo() {
        return $this->hasOne(\App\Models\Seo::class, 'id', 'seo_id');
    }

    public function seos() {
        return $this->hasMany(\App\Models\RelationSeoPostInfo::class, 'post_info_id', 'id');
    }

    public function contact() {
        return $this->hasOne(\App\Models\PostContact::class, 'post_info_id', 'id');
    }

    public function exchanges(){
        return $this->hasMany(\App\Models\RelationExchangeInfoPostInfo::class, 'post_info_id', 'id');
    }

    public function exchangeTags(){
        return $this->hasMany(\App\Models\RelationExchangeTagPostInfo::class, 'post_info_id', 'id');
    }

    public function exchangeOutstandings(){
        return $this->hasMany(\App\Models\RelationPostInfoExchangeOutstanding::class, 'post_info_id', 'id');
    }

    public function files(){
        return $this->hasMany(\App\Models\SystemFile::class, 'attachment_id', 'id')->where('relation_table', 'post_info');
    }
}
