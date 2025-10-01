<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Industry extends Model
{
    use HasFactory;
    protected $table = 'industry_info';
    protected $fillable = ['code', 'description', 'level', 'parent_code'];
    public $timestamps = false;

    public static function insertItem($params)
    {
        $id = 0;
        if (!empty($params)) {
            $model = new Industry();
            foreach ($params as $key => $value) {
                $model->{$key} = $value;
            }
            $model->save();
            $id = $model->id;
        }
        return $id;
    }

    public static function updateItem($id, $params)
    {
        $flag = false;
        if (!empty($id) && !empty($params)) {
            $model = self::find($id);
            foreach ($params as $key => $value) {
                $model->{$key} = $value;
            }
            $flag = $model->update();
        }
        return $flag;
    }

    // Quan hệ với ngành cha
    public function parent()
    {
        return $this->belongsTo(Industry::class, 'parent_code', 'code');
    }

    // Quan hệ với các ngành con
    public function children()
    {
        return $this->hasMany(Industry::class, 'parent_code', 'code');
    }

    // Quan hệ với các công ty có ngành chính là ngành này
    public function companies()
    {
        return $this->hasMany(Company::class, 'main_industry_code', 'code');
    }

    // Quan hệ với bảng relation_company_info_industry_info
    public function industryRelations()
    {
        return $this->hasMany(RelationCompanyInfoIndustryInfo::class, 'industry_code', 'code');
    }

    // Lấy tất cả mã ngành cấp 4 con (trực tiếp hoặc gián tiếp) bằng CTE
    public static function getLevelFourChildrenByCode($levelOneCode)
    {
        $query = DB::raw("
            WITH RECURSIVE industry_hierarchy AS (
                SELECT code, parent_code, level
                FROM industry_info
                WHERE code = :levelOneCode
                UNION ALL
                SELECT i.code, i.parent_code, i.level
                FROM industry_info i
                INNER JOIN industry_hierarchy ih ON i.parent_code = ih.code
            )
            SELECT code
            FROM industry_hierarchy
            WHERE level = 4
        ");

        $results = DB::select($query, ['levelOneCode' => $levelOneCode]);

        return collect($results)->pluck('code');
    }

    // Lấy tất cả công ty liên kết với mã ngành cấp 1 thông qua các mã cấp 4 con
    public static function getCompaniesByLevelOneCode($levelOneCode, $params = [])
    {
        // Lấy tất cả mã ngành cấp 4 con
        $levelFourCodes = self::getLevelFourChildrenByCode($levelOneCode);

        // Truy vấn công ty thông qua bảng quan hệ
        $query = Company::select('company_info.*')
            ->join('relation_company_info_industry_info as rcii', 'company_info.id', '=', 'rcii.company_info_id')
            ->whereIn('rcii.industry_code', $levelFourCodes);

        // Áp dụng điều kiện tìm kiếm nếu có
        if (!empty($params['search_name'])) {
            $searchName = $params['search_name'];
            $query->where(function ($q) use ($searchName) {
                $q->where('company_info.tax_code', 'LIKE', '%' . $searchName . '%')
                  ->orWhere('company_info.name', 'LIKE', '%' . $searchName . '%');
            });
        }

        // Thêm điều kiện ngôn ngữ nếu có
        if (!empty($params['language'])) {
            $query->withDefaultSeoForLanguage($params['language']);
        }

        // Sắp xếp và phân trang
        $query->orderBy('company_info.id', 'ASC')
              ->with('seo', 'industries');

        return $query->paginate($params['paginate'] ?? 10);
    }

    // Kiểm tra xem mã ngành có phải cấp 4 không
    public function isLevelFour()
    {
        return $this->level == 4;
    }

    // Lấy tất cả ngành cấp con của 1 nghành nghề bất kì
   public static function getAllChildIndustriesByCode($code)
    {
        $query = DB::raw("
            WITH RECURSIVE industry_hierarchy AS (
                SELECT *
                FROM industry_info
                WHERE code = :code
                UNION ALL
                SELECT i.*
                FROM industry_info i
                INNER JOIN industry_hierarchy ih ON i.parent_code = ih.code
            )
            SELECT *
            FROM industry_hierarchy
            WHERE code != :code2
        ");

        return collect(DB::select($query, [
            'code' => $code,
            'code2' => $code,
        ]));
    }

}