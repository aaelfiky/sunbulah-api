<?php

namespace Webkul\Topic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Core\Eloquent\TranslatableModel;
use Webkul\Topic\Contracts\Topic as TopicContract;

class Topic extends TranslatableModel implements TopicContract
{
    use HasFactory;

    protected $guarded = [];

    public $translatedAttributes = [
        'name'
    ];

    public function locales() {
        return $this->hasMany(TopicTranslation::class, 'topic_id');
    }
}
