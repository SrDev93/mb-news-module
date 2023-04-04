<?php

namespace Modules\News\Entities;

use App\Models\Brand;
use App\Models\Language;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Blogs\Entities\Blog;
use Modules\Blogs\Entities\BlogCategory;

class NewsCategory extends Model
{
    use HasFactory;
    use Sluggable;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function parent()
    {
        return $this->hasOne(NewsCategory::class, 'parent_id');
    }

    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    public function language()
    {
        return $this->hasOne(Language::class, 'lang', 'lang');
    }

    public function children()
    {
        return $this->hasMany(NewsCategory::class, 'parent_id')->with('children');
    }

    public function news() {
        return $this->hasMany(News::class, 'category_id', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\News\Database\factories\NewsCategoryFactory::new();
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
}
