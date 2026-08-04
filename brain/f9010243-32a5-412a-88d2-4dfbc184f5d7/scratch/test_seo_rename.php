<?php

// Include Composer autoloader
require __DIR__ . '/../../../../../Herd/amter/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../../../../../Herd/amter/bootstrap/app.php';

use App\Services\AutopilotImageService;
use App\Models\MediaItem;
use Illuminate\Support\Facades\Storage;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Testing SEO Rename Hooks ---\n";

$disk = Storage::disk('public');

// Ensure media-library directory exists in public storage
if (!$disk->exists('media-library')) {
    $disk->makeDirectory('media-library');
}

// 1. Create a dummy image file named with a ULID suffix (e.g. 01KSMA2HDTY8YFRYN6KD0SJ5QJ.png)
$originalFilename = '01KSMA2HDTY8YFRYN6KD0SJ5QJ.png';
$originalPath = 'media-library/' . $originalFilename;

// Let's create a 100x100 PNG image
$im = imagecreatetruecolor(100, 100);
$bg = imagecolorallocate($im, 255, 0, 0);
imagefill($im, 0, 0, $bg);
$absoluteOriginalPath = $disk->path($originalPath);
imagepng($im, $absoluteOriginalPath);
imagedestroy($im);

echo "Dummy original image created at: " . $absoluteOriginalPath . " (" . (file_exists($absoluteOriginalPath) ? 'Exists' : 'Missing') . ")\n";

// 2. Instantiate/mock a MediaItem
$mediaItem = new MediaItem();
$mediaItem->title = 'Man Searching';
$mediaItem->path = 'storage/' . $originalPath; // standard Filament/DB path structure

echo "Simulating Initial Model Saving Event Hook...\n";
$ext = strtolower(pathinfo($mediaItem->path, PATHINFO_EXTENSION));
if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    $titleSlug = \Illuminate\Support\Str::slug($mediaItem->title);
    $newPath = AutopilotImageService::convertAndReplaceOriginal($mediaItem->path, $titleSlug);
    if ($newPath) {
        $mediaItem->path = $newPath;
    }
}

echo "After initial save:\n";
echo " - Updated path: " . $mediaItem->path . "\n";
$absoluteNewPath = $disk->path(ltrim($mediaItem->path, 'storage/'));
echo " - Physical file exists: " . (file_exists($absoluteNewPath) ? 'YES' : 'NO') . "\n";

// Test 2: Simulating second save where title hasn't changed
echo "\n--- Test 2: Saving again with NO title change ---\n";
$oldPath = $mediaItem->path;
$ext2 = strtolower(pathinfo($mediaItem->path, PATHINFO_EXTENSION));
if (in_array($ext2, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    $titleSlug = \Illuminate\Support\Str::slug($mediaItem->title);
    $newPath2 = AutopilotImageService::convertAndReplaceOriginal($mediaItem->path, $titleSlug);
    if ($newPath2) {
        $mediaItem->path = $newPath2;
    }
}
echo " - Updated path: " . $mediaItem->path . "\n";
echo " - Path remains identical: " . ($mediaItem->path === $oldPath ? 'YES' : 'NO') . "\n";
$absolutePath2 = $disk->path(ltrim($mediaItem->path, 'storage/'));
echo " - Physical file exists: " . (file_exists($absolutePath2) ? 'YES' : 'NO') . "\n";

// Test 3: Simulating title change (e.g. from 'Man Searching' to 'Man Searching Books')
echo "\n--- Test 3: Title change to 'Man Searching Books' ---\n";
$mediaItem->title = 'Man Searching Books';
$oldPath = $mediaItem->path;
$ext3 = strtolower(pathinfo($mediaItem->path, PATHINFO_EXTENSION));
if (in_array($ext3, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    $titleSlug = \Illuminate\Support\Str::slug($mediaItem->title);
    $newPath3 = AutopilotImageService::convertAndReplaceOriginal($mediaItem->path, $titleSlug);
    if ($newPath3) {
        $mediaItem->path = $newPath3;
    }
}
echo " - Updated path: " . $mediaItem->path . "\n";
$absolutePath3 = $disk->path(ltrim($mediaItem->path, 'storage/'));
echo " - New physical file exists: " . (file_exists($absolutePath3) ? 'YES' : 'NO') . "\n";
echo " - Old physical file deleted: " . (!file_exists($disk->path(ltrim($oldPath, 'storage/'))) ? 'YES' : 'NO') . "\n";

// Cleanup physical files
if (file_exists($absolutePath3)) {
    @unlink($absolutePath3);
}

echo "Test complete!\n";
