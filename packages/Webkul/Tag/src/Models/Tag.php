<?php

namespace Webkul\Tag\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Core\Eloquent\TranslatableModel;
use Webkul\Tag\Contracts\Tag as TagContract;

class Tag extends TranslatableModel implements TagContract
{
    use HasFactory;

    protected $guarded = [];

    public $translatedAttributes = [
        'name'
    ];

    public function locales() {
        return $this->hasMany(TagTranslation::class, 'tag_id');
    }
}
