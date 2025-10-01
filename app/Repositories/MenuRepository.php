<?php

namespace App\Repositories;

use App\Models\Page;
use App\Models\Category;
use App\Models\Exchange;
use App\Models\CategoryBlog;

class MenuRepository
{
    public function getMenuData($language)
    {
        return [
            'infoPageAboutUs'       => $this->getPageBySlug('ve-chung-toi'),
            'categoryBlogParent'    => $this->getCategoryBlogs(),
            'infoPageContact'       => $this->getPageBySlug('lien-he'),
            'language'              => $language,
        ];
    }

    protected function getPageBySlug($slug)
    {
        return Page::whereHas('seo', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->with(['seo', 'seos'])
            ->first();
    }

    protected function getCategories()
    {
        return Category::select('category_info.*')
            ->with(['seo', 'seos', 'tags.infoTag'])
            ->join('seo', 'seo.id', '=', 'category_info.seo_id')
            ->orderBy('seo.ordering', 'DESC')
            ->get();
    }

    protected function getExchanges()
    {
        $exchanges = Exchange::getTreeCategory();
        return $exchanges[0] ?? null;
    }

    protected function getCategoryBlogs()
    {
        $blogs = CategoryBlog::getTreeCategory();
        return $blogs[0] ?? null;
    }
}