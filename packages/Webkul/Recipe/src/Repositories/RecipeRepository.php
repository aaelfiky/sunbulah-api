<?php

namespace Webkul\Recipe\Repositories;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Webkul\Recipe\Models\RecipeTranslation;
use Webkul\Recipe\Models\Recipe;
use Webkul\Product\Models\Product;
use Webkul\Tag\Models\Tag;
use Webkul\Tag\Models\TagTranslation;
use Webkul\Topic\Models\Topic;
use Webkul\Topic\Models\TopicTranslation;
use Illuminate\Pagination\Paginator;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Container\Container as App;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RecipeRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @param \Illuminate\Container\Container $app
     *
     * @return void
     */
    public function __construct(
        App $app
    )
    {
        parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    function model()
    {
        return 'Webkul\Recipe\Contracts\Recipe';
    }

    /**
     * @param array $data
     *
     * @return \Webkul\Recipe\Contracts\Recipe
     */
    /**
     * Create recipe.
     *
     * @param  array  $data
     * @return \Webkul\Recipe\Contracts\Recipe
     */
    public function create(array $data)
    {
        Event::dispatch('catalog.recipe.create.before');

        $locale = core()->getRequestedLocaleCode();
        
        $_model = app()->make($this->model());

        $fillable = $_model->translatedAttributes;

        $data_ = $data;

        $data[$locale] = array_intersect_key($data, array_flip($fillable));

        unset($data["name"]);
        unset($data["preparation_time"]);
        unset($data["serves"]);
        unset($data["cooking_time"]);
        unset($data["image_desktop"]);
        unset($data["image_mobile"]);
        unset($data["video_link"]);
        // unset($data["slug"]);
        unset($data["instructions"]);
        unset($data["seo_title"]);
        unset($data["seo_desc"]);
        unset($data["seo_image"]);
        unset($data["seo_keywords"]);

        $data[$locale]["seo"] = [
            "title" => $data["seo_title"] ?? "",
            "description" => $data["seo_desc"] ?? "",
            "image" => $data["seo_image"] ?? "",
            "keywords" => $data["seo_keywords"] ?? ""
        ];

        unset($data[$locale]["image_desktop"]);
        unset($data[$locale]["image_mobile"]);

        $recipe = $this->model->create($data);

        DB::update('update recipes set slug = ? where id = ?', [$data["slug"], $recipe->id]);

        $this->uploadImages($data_, $recipe);

        Event::dispatch('catalog.recipe.create.after', $recipe);

        return $recipe;
    }

    /**
     * @param array  $data
     * @param int    $id
     * @param string $attribute
     *
     * @return \Webkul\Recipe\Contracts\Recipe
     */
    public function update(array $data, $id, $attribute = "id")
    {
        $recipe = $this->find($id);

        if (isset($data["products"]))
        {
            $recipe->products()->sync($data["products"]);
        }

        Event::dispatch('catalog.recipe.update.before', $id);

        // $data = $this->setSameAttributeValueToAllLocale($data, 'slug');
        $locale = core()->getRequestedLocaleCode();
        $_model = app()->make($this->model());

        $fillable = $_model->translatedAttributes;

        $new_tags = $data["new_tags"];

        $added_new_tags = [];

        if (isset($new_tags) && count($new_tags) > 0) {
            foreach ($new_tags as $new_tag) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $new_tag)));
                $created = Tag::create(["slug" => $slug]);
                $translation = TagTranslation::create([
                    "name" => $new_tag,
                    "locale" => $locale,
                    "tag_id" => $created->id
                ]);
                array_push($added_new_tags, $created->id);
            }
        }

        if (count($added_new_tags) > 0)
        {
            $recipe->tags()->sync($added_new_tags);
            unset($data["new_tags"]);
        }

        if (isset($data["tags"]))
        {
            $recipe->tags()->sync($data["tags"]);
            unset($data["tags"]);
        }

        $new_topic = $data["new_topic"];

        if (isset($new_topic) && strlen($new_topic) > 0) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $new_topic)));
            $created = Topic::create(["slug" => $slug]);
            $translation = TopicTranslation::create([
                "name" => $new_topic,
                "locale" => $locale,
                "topic_id" => $created->id
            ]);

            $recipe->topic_id = $created->id;
            $recipe->save();
        }

        if (isset($data["topic_id"]) && strlen($data["topic_id"]) > 0)
        {
            $recipe->topic_id = $data["topic_id"];
            $recipe->save();
        }


        unset($data["topic_id"]);
        unset($data["new_topic"]);
        unset($data["locale"]); 
        unset($data["products"]);

        if (isset($data["image_desktop"]) && count($data["image_desktop"]) > 0 && strlen($data["image_desktop"]["image_0"]) == 0) {
            unset($data["image_desktop"]);
        }

        if (isset($data[$locale]["image_desktop"]) && count($data[$locale]["image_desktop"]) > 0 && strlen($data[$locale]["image_desktop"]["image_0"]) == 0) {
            unset($data[$locale]["image_desktop"]);
        }

        if (isset($data["image_mobile"]) && count($data["image_mobile"]) > 0 && strlen($data["image_mobile"]["image_0"]) == 0) {
            unset($data["image_mobile"]);
        }

        if (isset($data[$locale]["image_mobile"]) && count($data[$locale]["image_mobile"]) > 0 && strlen($data[$locale]["image_mobile"]["image_0"]) == 0) {
            unset($data[$locale]["image_mobile"]);
        }

        if (isset($data[$locale]["video"]) && strlen($data[$locale]["video"]) == 0) {
            unset($data[$locale]["video"]);
        }

        if (isset($data[$locale]["recipe_card"]["image"]) && strlen($data[$locale]["recipe_card"]["image"]) == 0) {
            unset($data[$locale]["recipe_card"]["image"]);
        }

        $recipe->update($data);

        DB::update('update recipes set slug = ? where id = ?', [$data["slug"], $id]);

        $this->uploadImages($data, $recipe);
        if (isset($data[$locale]["video"]))
            $this->uploadVideo($data[$locale]["video"], $recipe);

        Event::dispatch('catalog.recipe.update.after', $id);

        return $recipe;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function delete($id)
    {
        Event::dispatch('catalog.recipe.delete.before', $id);

        parent::delete($id);

        Event::dispatch('catalog.recipe.delete.after', $id);
    }

    /**
     * Retrive recipe from slug.
     *
     * @param string $slug
     * @return \Webkul\Recipe\Contracts\Recipe
     */
    public function findBySlugOrFail($slug)
    {
        $recipe = $this->model->whereTranslation('slug', $slug)->first();

        if ($recipe) {
            $recipe->similar_recipes = $this->model->whereTranslation('main_product_id', $recipe->main_product_id)->get();
            return $recipe;
        }

        throw (new ModelNotFoundException)->setModel(
            get_class($this->model), $slug
        );
    }


     /**
     * @param int $categoryId
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProductsRelatedToCategory($categoryId = null)
    {
        $qb = $this->model
            ->leftJoin('product_categories', 'products.id', '=', 'product_categories.product_id');

        if ($categoryId) {
            $qb->where('product_categories.category_id', $categoryId);
        }

        return $qb->get();
    }

   /**
     * Upload recipe's images.
     *
     * @param  array  $data
     * @param  \Webkul\Recipe\Contracts\Recipe  $recipe
     * @param  string $type
     * @return void
     */
    public function uploadImages($data, $recipe, $locale = "en", $type = "image_desktop", $multiple = false)
    {
        $recipe_translation = RecipeTranslation::firstOrCreate([
            "locale" => $locale,
            "recipe_id" => $recipe->id
        ]);

        if (isset($data[$type])) {
            $request = request();

            foreach ($data[$type] as $imageId => $image) {
                $file = $type . '.' . $imageId;
                $dir = 'recipe/' . $recipe->id . '_' . $recipe_translation->id;

                if ($request->hasFile($file)) {
                    if ($recipe_translation->{$type}) {
                        Storage::delete($recipe_translation->{$type});
                    }

                    $recipe_translation->{$type} = $request->file($file)->store($dir);
                    $recipe_translation->save();
                }

                if (!$multiple) {
                    break;
                }
            }
        } else {
            // if ($recipe_translation->{$type}) {
            //     Storage::delete($recipe_translation->{$type});
            // }

            // $recipe_translation->{$type} = null;
            // $recipe_translation->save();
        }
    }

     /**
     * Upload recipe's images.
     *
     * @param  array  $data
     * @param  \Webkul\Recipe\Contracts\Recipe  $recipe
     * @return void
     */
    public function uploadVideo($data, $recipe, $locale = "en")
    {
        $recipe_translation = RecipeTranslation::firstOrCreate([
            "locale" => $locale,
            "recipe_id" => $recipe->id
        ]);
        
        if (isset($data)) {
            $request = request();
            
            $file = "$locale.".'video';
            $dir = 'videos/recipes/' . $recipe->id . '_' . $recipe_translation->id;
            
            if ($request->hasFile($file)) {
                if ($recipe_translation->video) {
                    Storage::delete($recipe_translation->video);
                }

                $custom_file_name = 'video_' . $recipe->id . '.' . $request->file($file)->getClientOriginalExtension();
                $recipe_translation->video = $request->file($file)->storeAs($dir, $custom_file_name);
                $recipe_translation->save();
            }
        }
    }

    public function getAll()
    {
        
        $query = $this->model->query();
        if (request()->has('category_id')) {
            $product_ids = Product::whereHas('categories', function ($q) {
                $q->where("category_id", request()->input("category_id"));
            })->select('products.id')->get()->pluck('id')->toArray();

            $recipe_ids = RecipeTranslation::whereIn('main_product_id', $product_ids)->select('recipe_id')->get()->pluck('recipe_id')->toArray();
            $query = $query->whereIn('id', $recipe_ids);
        }

        if (request()->has('product_id')) {
            $recipe_ids = RecipeTranslation::where('main_product_id', request()->input("product_id"))->select('recipe_id')->get()->pluck('recipe_id')->toArray();
            $query = $query->whereIn('id', $recipe_ids);
        }

        if (request()->has('cooking_time')) {
            $recipe_ids = RecipeTranslation::where('cooking_time', request()->input("cooking_time"))->select('recipe_id')->get()->pluck('recipe_id')->toArray();
            $query = $query->whereIn('id', $recipe_ids);
        }

        if (request()->has('tags')) {
           // TODO: Add Recipe Tags
        }

        return $query->get();
    }
}