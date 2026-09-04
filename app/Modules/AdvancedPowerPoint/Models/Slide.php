<?php

namespace App\Modules\AdvancedPowerPoint\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $table = 'slides';

    protected $fillable = [
        'presentation_id',
        'title',
        'order',
        'layout',
        'background',
        'transition',
        'animation',
        'settings',
    ];

    protected $casts = [
        'settings' => 'json',
        'order' => 'integer',
    ];

    /**
     * روابط
     */
    public function presentation()
    {
        return $this->belongsTo(Presentation::class);
    }

    public function elements()
    {
        return $this->hasMany(SlideElement::class)->orderBy('order');
    }

    /**
     * متدهای کمکی
     */
    public function getPreviewUrlAttribute(): string
    {
        return route('slide.preview', $this->id);
    }

    public function duplicate(): Slide
    {
        $newSlide = $this->replicate();
        $newSlide->order = $this->presentation->slides()->count();
        $newSlide->save();

        foreach ($this->elements as $element) {
            $newElement = $element->replicate();
            $newElement->slide_id = $newSlide->id;
            $newElement->save();
        }

        return $newSlide;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($slide) {
            $slide->elements()->delete();
            $slide->presentation->clearCache();
        });
    }
}
