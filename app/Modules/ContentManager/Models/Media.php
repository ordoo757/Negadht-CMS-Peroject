<?php

namespace App\Modules\ContentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'filename',
        'original_name',
        'path',
        'url',
        'mime_type',
        'size',
        'alt',
        'caption',
        'description',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * روابط
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * متدهای کمکی
     */
    public function getSizeLabelAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getThumbnailAttribute(): string
    {
        if (str_starts_with($this->mime_type, 'image/')) {
            return $this->url;
        }

        return asset('images/file-icon.png');
    }

    public function deleteFile(): bool
    {
        if (Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        return $this->delete();
    }

    /**
     * متدهای ثابت
     */
    public static function upload($file, $path = 'uploads', $alt = null): self
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $storedPath = $file->storeAs($path, $filename, 'public');

        return self::create([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'url' => Storage::url($storedPath),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt' => $alt,
            'user_id' => auth()->id(),
            'is_active' => true,
        ]);
    }
}
