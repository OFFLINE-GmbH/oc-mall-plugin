<?php

namespace OFFLINE\Mall\Classes\Traits\Category;

use Cache;
use October\Rain\Exception\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OFFLINE\Mall\Models\Category;

trait Slug
{
    /**
     * Returns the slug of this category prepended by
     * the slugs of each parent category.
     *
     * @return string
     */
    public function getNestedSlugAttribute()
    {
        return $this->getParentsAndSelf()->map(function (Category $category) {
            $category->setTranslateContext($this->getTranslateContext());

            return $category->slug;
        })->implode('/');
    }

    /**
     * Make sure the category's slug is not yet in use.
     *
     * @param Category $model
     *
     * @throws ValidationException
     */
    public function validateUniqueSlug(Category $model)
    {
        foreach ($model->getLocales() as $locale) {
            $slug = '';
            $model->setTranslateContext($locale);
            if ($model->parent_id) {
                $parent = Category::find($model->parent_id);
                $parent->setTranslateContext($locale);
                $prefix = $parent->nested_slug;
                $slug   .= trim($prefix, '/') . '/';
            }
            $slug .= trim($model->slug, '/');
            $map  = $model->getSlugMap($locale);

            if (array_key_exists($slug, $map) && $map[$slug] !== $model->id) {
                throw new ValidationException(['slug' => trans('offline.mall::lang.common.slug_unique')]);
            }
        }
    }


    /**
     * This method is used in components to find the category either
     * by slug (if $id = ":slug") or by id.
     *
     * @param $slug
     * @param $id
     *
     * @return mixed
     */
    public static function bySlugOrId($slug, $id)
    {
        if ($id === ':slug') {
            return self::getByNestedSlug($slug);
        }

        return self::findOrFail($id);
    }

    public static function getByNestedSlug(string $slug, array $with = [])
    {
        $slug = trim($slug, ' /');

        $slugMap = (new Category)->getSlugMap();
        if ( ! isset($slugMap[$slug])) {
            throw new ModelNotFoundException(sprintf('Category with slug %s not found.', $slug));
        }

        return Category::with($with)->findOrFail($slugMap[$slug]);
    }

    /**
     * Returns a cached map of category_ids and slug pairs.
     */
    public function getSlugMap($locale = null)
    {
        $locale = $this->getLocale($locale);

        return \Cache::rememberForever($this->mapCacheKey($locale), function () use ($locale) {
            $map = [];

            $buildSlugMap = function (
                ?Category $parent = null,
                array &$map,
                string $base = ''
            ) use (
                &$buildSlugMap,
                $locale
            ) {
                $parent->setTranslateContext($locale);
                $slug       = trim($base . '/' . $parent->slug, '/');
                $map[$slug] = $parent->id;
                foreach ($parent->children as $child) {
                    $buildSlugMap($child, $map, $slug);
                }
            };

            $model = new Category();
            foreach ($model->getAllRoot() as $parent) {
                $buildSlugMap($parent, $map);
            }

            return $map;
        });
    }
}
