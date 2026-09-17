<?php

namespace App\Data;

use App\Models\FurnitureImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelData\Data;

class FurnitureImageData extends Data
{
    public function __construct(
        public int $images_id,
        public int $furniture_id,
        public string $path_image,
        public bool $is_main,
    ) {
        //
    }

    public static function fromModel(FurnitureImage $image): self
    {
        return new self(
            images_id: $image->images_id,
            furniture_id: $image->furniture_id,
            path_image: self::resolveUrl($image->path_image),
            is_main: $image->is_main,
        );
    }

    /**
     * Convert a stored relative path into a browser-accessible URL.
     */
    public static function resolveUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://', '/', 'data:'])) {
            return $path;
        }

        $url = Storage::disk('public')->url($path);

        if (Str::startsWith($url, ['http://', 'https://'])) {
            $pathOnly = parse_url($url, PHP_URL_PATH);

            if ($pathOnly !== false && $pathOnly !== null) {
                return $pathOnly;
            }
        }

        return $url;
    }
}
