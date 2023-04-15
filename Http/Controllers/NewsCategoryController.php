<?php

namespace Modules\News\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Modules\News\Entities\NewsCategory;

class NewsCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (\request()->session()->has('brand_id')){
            $categories = NewsCategory::where('brand_id', \request()->session()->get('brand_id'))->whereNull('parent_id')->orderBy('sort_id')->get();
        }elseif (Auth::user()->brand_id) {
            $categories = NewsCategory::where('brand_id', Auth::user()->brand_id)->whereNull('parent_id')->orderBy('sort_id')->get();
        }else {
            $categories = NewsCategory::whereNull('parent_id')->orderBy('sort_id')->get();
        }

        return view('news::category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('news::category.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'lang' => 'required',
            'brand_id' => 'required',
        ]);
        try {
            $ac = NewsCategory::create([
                'lang' => $request->lang,
                'brand_id' => $request->brand_id,
                'name' => $request->name,
                'slug' => $request->slug
            ]);

            if (isset($request->banner)){
                $ac->banner = file_store($request->banner, 'assets/uploads/photos/news_category_banner/', 'photo_');
            }
            $ac->save();

            return redirect()->route('NewsCategory.index')->with('flash_message', 'با موفقیت ثبت شد');
        }catch (\Exception $e){
            return redirect()->back()->with('err_message', 'خطایی رخ داده است، لطفا مجددا تلاش نمایید');
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('news::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(NewsCategory $NewsCategory)
    {
        return view('news::category.edit', compact('NewsCategory'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, NewsCategory $NewsCategory)
    {
        try {
            if ($request->lang) {
                $NewsCategory->lang = $request->lang;
            }
            if ($request->brand_id) {
                $NewsCategory->brand_id = $request->brand_id;
            }
            $NewsCategory->name = $request->name;
            $NewsCategory->slug = $request->slug;

            if (isset($request->banner)){
                if ($NewsCategory->banner){
                    File::delete($NewsCategory->banner);
                }
                $NewsCategory->banner = file_store($request->banner, 'assets/uploads/photos/news_category_banner/', 'photo_');
            }

            $NewsCategory->save();

            return redirect()->route('NewsCategory.index')->with('flash_message', 'بروزرسانی با موفقیت انجام شد');
        }catch (\Exception $e){
            return redirect()->back()->with('err_message', 'خطایی رخ داده است، لطفا مجددا تلاش نمایید');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(NewsCategory $NewsCategory)
    {
        try {
            $NewsCategory->delete();

            return redirect()->route('NewsCategory.index')->with('flash_message', 'با موفقیت حذف شد');
        }catch (\Exception $e){
            return redirect()->back()->with('err_message', 'خطایی رخ داده است، لطفا مجددا تلاش نمایید');
        }
    }

    /**
     * Sort Item.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function sort_item(Request $request)
    {
        $category_item_sort = json_decode($request->sort);
        $this->sort_category($category_item_sort, null);
    }

    /**
     * Sort Category.
     *
     *
     * @param $category_items
     * @param $parent_id
     */
    private function sort_category(array $category_items, $parent_id)
    {
        foreach ($category_items as $index => $category_item) {
            $item = NewsCategory::findOrFail($category_item->id);
            $item->sort_id = $index + 1;
            $item->parent_id = $parent_id;
            $item->save();
            if (isset($category_item->children)) {
                $this->sort_category($category_item->children, $item->id);
            }
        }
    }
}
