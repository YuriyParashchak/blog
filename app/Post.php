<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use Sluggable;
    protected $fillable = ['title','content'];
    const IS_DRAFT = 0;
    const IS_PUBLIC = 1;
    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }


   public function category()
   {
       return $this->belongsTo(Category::class);
   }

    public function author()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'post_tags',
            'post_id',
            'tag_id'
        );
    }

    public static function add($fields /*$title,$content*/)
    {
        $post = new static ;
       // $post->title = $title;
       // $post->content = $content;
        $post ->fill(/*[
           'title'=>$title,
            'content'=>$content,
            ]*/$fields);
        $post->user_id=1;
        $post->save();
        return $post;

    }
    public function edit($fields)
    {
        $this ->fill($fields);
        $this->save();

    }

    public function remove()
    {
        if($this->image != null)
            Storage::delete('uploads/'. $this->image);
        $this ->delete();

    }

    /**
     * @param $image
     */
    public function uploadImage($image)
    {
        if($image == null){return;};
        if($this->image != null)
       Storage::delete('uploads/'. $this->image);
      //  Storage::disk('public')->delete('uploads/'. $this->image);
        //unlink($filename);
        $filename = str_random(10) . '.' . $image->extension();
        $image->storeAs('uploads',$filename);
        $this->image = $filename;
        $this->save();

    }

    public function setCategory($id)
    {
        if($id==null){return;}
        $this->category_id = $id;
        $this->save();
    }

    public function setTags($ids)
    {
        if($ids==null){return;}
        $this->tags()->sync($ids);
        $this->save();
    }

    public function setDraft()
    {

        $this->status = Post::IS_DRAFT;
        $this->save();
    }

    public function setPublic()
    {

        $this->status = Post::IS_PUBLIC;
        $this->save();
    }

    public function toggleStatus($value)
    {

       if($value==null)
       {
          return $this->setDraft();
       }

          return $this->setPublic();


    }

    public function setFeatured()
    {
        $this->is_featured = 1;
        $this->save();
    }

    public function setStandart()
    {
        $this->is_featured = 0;
        $this->save();
    }

    public function toggleFeature($value)
    {
        if($value==null)
        {
            return $this->setFeatured();
        }

        return $this->setStandart();
    }

    public function getImage()
    {
        if($this->image==null)
        {
            return 'img/no-image.png';
        }
        return '/uploads/' . $this->image;
    }
    public function setDateAttribute($value)
    {
       $date =  Carbon::createFromFormat('d/m/y',$value)->format('y-m-d');
       $this->attributes['date']=$date;
    }

    public function getDateAttribute($value)
    {
        $date = Carbon::createFromFormat('Y-m-d', $value)->format('d/m/y');

        return $date;
    }

    public function getCategoryTitle()
    {
        return ($this->category != null)
            ?   $this->category->title
            :   'Нет категории';
    }
    public function getCategoryID()
    {
        return $this->category != null ? $this->category->id : null;
    }
    public function getTagsTitles()
    {
        return (!$this->tags->isEmpty())
            ?   implode(', ', $this->tags->pluck('title')->all())
            : 'Нет тегов';
    }
}
