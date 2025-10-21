<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\AdImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AdImageSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure public disk exists
        Storage::disk('public')->makeDirectory('ads');

        Ad::query()->withCount('images')->get()->each(function (Ad $ad) {
            if ($ad->images_count > 0) return; // skip ads that already have images

            $count = rand(2, 4);
            for ($i = 0; $i < $count; $i++) {
                $isCover = $i === 0;
                $path = $this->makeSvgPlaceholder($ad, $i + 1);

                AdImage::create([
                    'ad_id' => $ad->id,
                    'path' => $path,
                    'is_cover' => $isCover,
                    'position' => $i + 1,
                ]);
            }
        });
    }

    private function makeSvgPlaceholder(Ad $ad, int $index): string
    {
        $w = 800; $h = 600;
        $bg = sprintf('#%02x%02x%02x', rand(100,200), rand(100,200), rand(100,200));
        $fg = '#ffffff';
        $title = htmlspecialchars(mb_substr($ad->title, 0, 28));
        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="$w" height="$h" viewBox="0 0 $w $h">
  <rect width="100%" height="100%" fill="$bg"/>
  <text x="50%" y="45%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="36" fill="$fg">$title</text>
  <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="$fg">Slika $index</text>
  <rect x="20" y="20" width="120" height="40" rx="6" ry="6" fill="rgba(0,0,0,0.2)"/>
  <text x="80" y="45" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="$fg">Demo</text>
  Sorry, your browser does not support inline SVG.
  </svg>
SVG;

        $dir = 'ads/'.$ad->id;
        $filename = 'placeholder_'.$index.'.svg';
        $path = $dir.'/'.$filename;
        Storage::disk('public')->put($path, $svg);
        return $path;
    }
}

