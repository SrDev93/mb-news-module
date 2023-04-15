<?php

namespace Modules\News\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Modules\Blogs\Entities\Tag;
use Modules\News\Entities\News;
use Modules\News\Entities\NewsCategory;
use Modules\News\Entities\NewsTag;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (\request()->session()->has('brand_id')){
            $items = News::where('brand_id', \request()->session()->get('brand_id'))->get();
        }elseif (Auth::user()->brand_id) {
            $items = News::where('brand_id', Auth::user()->brand_id)->get();
        }else {
            $items = News::all();
        }

        return view('news::index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (\request()->session()->has('brand_id')){
            $categories = NewsCategory::where('brand_id', \request()->session()->get('brand_id'))->get();
            $tags = Tag::where('brand_id', \request()->session()->get('brand_id'))->get();
        }elseif (Auth::user()->brand_id) {
            $categories = NewsCategory::where('brand_id', Auth::user()->brand_id)->get();
            $tags = Tag::where('brand_id', Auth::user()->brand_id)->get();
        }else {
            $categories = NewsCategory::all();
            $tags = Tag::all();
        }

        return view('news::create', compact('categories', 'tags'));
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
            'title' => 'required',
        ]);
        try {
            $news = News::create([
                'lang' => $request->lang,
                'brand_id' => $request->brand_id,
                'user_id' => Auth::id(),
                'category_id' => $request->category_id,
                'title' => $request->title,
                'slug' => $request->slug,
                'short_text' => $request->short_text,
                'body' => $request->body,
                'image_alt' => $request->image_alt,
                'image' => (isset($request->image)?file_store($request->image, 'assets/uploads/photos/news_images/','photo_'):null),
                'banner' => (isset($request->banner)?file_store($request->banner, 'assets/uploads/photos/news_banners/','photo_'):null)
            ]);

            if (isset($request->tags)){
                foreach ($request->tags as $tag){
                    $bt = NewsTag::create([
                        'news_id' => $news->id,
                        'tag_id' => $tag
                    ]);
                }
            }

            return redirect()->route('News.index')->with('flash_message', 'با موفقیت ثبت شد');
        }catch (\Exception $e){
            return redirect()->back()->withInput()->with('err_message', 'خطایی رخ داده است، لطفا مجددا تلاش نمایید');
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
    public function edit(News $News)
    {
        if (\request()->session()->has('brand_id')){
            $categories = NewsCategory::where('brand_id', \request()->session()->get('brand_id'))->get();
            $tags = Tag::where('brand_id', \request()->session()->get('brand_id'))->get();
        }elseif (Auth::user()->brand_id) {
            $categories = NewsCategory::where('brand_id', Auth::user()->brand_id)->get();
            $tags = Tag::where('brand_id', Auth::user()->brand_id)->get();
        }else {
            $categories = NewsCategory::all();
            $tags = Tag::all();
        }

        $News_tags = $News->tags->pluck('tag_id')->toArray();

        return view('news::edit', compact('News', 'categories', 'tags', 'News_tags'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, News $News)
    {
        try {
            if ($request->lang) {
                $News->lang = $request->lang;
            }
            if ($request->brand_id) {
                $News->brand_id = $request->brand_id;
            }
            $News->category_id = $request->category_id;
            $News->title = $request->title;
            $News->slug = $request->slug;
            $News->short_text = $request->short_text;
            $News->body = $request->body;
            $News->image_alt = $request->image_alt;
            if (isset($request->image)) {
                if ($News->image){
                    File::delete($News->image);
                }
                $News->image = file_store($request->image, 'assets/uploads/photos/news_images/','photo_');
            }
            if (isset($request->banner)) {
                if ($News->banner){
                    File::delete($News->banner);
                }
                $News->banner = file_store($request->banner, 'assets/uploads/photos/news_banners/','photo_');
            }

            $News->save();

            if (isset($request->tags)) {
                $deleted = NewsTag::where('news_id', $News->id)->whereNotIn('tag_id', $request->tags)->delete();
            }else{
                $deleted = NewsTag::where('news_id', $News->id)->delete();
            }

            if (isset($request->tags)){
                foreach ($request->tags as $tag){
                    $old = NewsTag::where('news_id', $News->id)->where('tag_id', $tag)->first();
                    if (!$old) {
                        $bt = NewsTag::create([
                            'news_id' => $News->id,
                            'tag_id' => $tag
                        ]);
                    }
                }
            }

            return redirect()->route('News.index')->with('flash_message', 'با موفقیت بروزرسانی شد');
        }catch (\Exception $e){
            return redirect()->back()->withInput()->with('err_message', 'خطایی رخ داده است، لطفا مجددا تلاش نمایید');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(News $News)
    {
        try {
            $News->delete();

            return redirect()->back()->with('flash_message', 'با موفقیت حذف شد');
        }catch (\Exception $e){
            return redirect()->back()->with('err_message', 'خطایی رخ داده است، لطفا مجددا تلاش نمایید');
        }
    }
}
