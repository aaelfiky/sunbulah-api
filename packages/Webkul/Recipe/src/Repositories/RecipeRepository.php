<?php

namespace Webkul\Recipe\Repositories;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Webkul\Recipe\Models\RecipeTranslation;
use Webkul\Recipe\Models\Recipe;
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

        // if (isset($data['locale']) && $data['locale'] == 'all') {
        //     $model = app()->make($this->model());

        //     foreach (core()->getAllLocales() as $locale) {
        //         foreach ($model->translatedAttributes as $attribute) {
        //             if (isset($data[$attribute])) {
        //                 $data[$locale->code][$attribute] = $data[$attribute];
        //                 $data[$locale->code]['locale_id'] = $locale->id;
        //             }
        //         }
        //     }
        // }


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

        Event::dispatch('catalog.recipe.update.before', $id);

        // $data = $this->setSameAttributeValueToAllLocale($data, 'slug');
        $locale = core()->getRequestedLocaleCode();
        $_model = app()->make($this->model());

        $fillable = $_model->translatedAttributes;
        $data[$locale] = array_intersect_key($data, array_flip($fillable));

        unset($data["locale"]); 
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

        $recipe->update($data);

        DB::update('update recipes set slug = ? where id = ?', [$data["slug"], $id]);

        $this->uploadImages($data, $recipe);

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
            if ($recipe_translation->{$type}) {
                Storage::delete($recipe_translation->{$type});
            }

            $recipe_translation->{$type} = null;
            $recipe_translation->save();
        }


    }
}