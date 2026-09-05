<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */
<?php

namespace App\Modules\AdvancedPowerPoint\Models;

use Illuminate\Database\Eloquent\Model;

class SlideElement extends Model
{
    protected $table = 'slide_elements';

    protected $fillable = [
        'slide_id',
        'type',
        'content',
        'style',
        'position',
        'size',
        'order',
        'animation',
        'settings',
    ];

    protected $casts = [
        'style' => 'json',
        'position' => 'json',
        'size' => 'json',
        'settings' => 'json',
        'order' => 'integer',
    ];

    /**
     * روابط
     */
    public function slide()
    {
        return $this->belongsTo(Slide::class);
    }

    /**
     * متدهای کمکی
     */
    public function getRenderedContentAttribute(): string
    {
        switch ($this->type) {
            case 'text':
                return $this->content;
            case 'image':
                return '<img src="' . $this->content . '" alt="Slide element">';
            case 'shape':
                return '<div class="shape" style="background: ' . $this->style['background'] . '"></div>';
            case 'chart':
                return '<div class="chart" data-chart="' . $this->content . '"></div>';
            case 'table':
                return '<table class="table">' . $this->content . '</table>';
            default:
                return $this->content;
        }
    }
}
