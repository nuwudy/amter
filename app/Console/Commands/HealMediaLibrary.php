<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MediaItem;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;

class HealMediaLibrary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:heal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Heal broken Media Library items that were renamed and orphaned during Unit saves';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = Storage::disk('public');
        $mediaItems = MediaItem::where('type', 'image')->get();

        $this->info("Scanning " . $mediaItems->count() . " image MediaItems...");
        $healedCount = 0;

        foreach ($mediaItems as $item) {
            $path = $item->path;
            if (empty($path)) {
                continue;
            }

            // Clean path (remove starting slash or storage/ prefix)
            $cleanPath = ltrim($path, '/');
            if (str_starts_with($cleanPath, 'storage/')) {
                $cleanPath = substr($cleanPath, 8);
            }

            if ($disk->exists($cleanPath)) {
                $this->line(" - [OK] {$item->title} ({$cleanPath}) exists on disk.");
                continue;
            }

            $this->warn(" - [MISSING] {$item->title} ({$cleanPath}) is missing from disk!");

            // Extract the suffix (e.g. 01KT681H from welcome-to-kerala-new-01KT681H.webp)
            $filename = pathinfo($cleanPath, PATHINFO_FILENAME);
            $suffix = '';
            if (preg_match('/-([0-9A-Z]{8})$/i', $filename, $matches)) {
                $suffix = $matches[1];
            } else {
                // If it is just 8 chars (or anything else), try to use the last 8 chars
                $suffix = substr($filename, -8);
            }

            if (empty($suffix)) {
                $this->error("   Could not determine suffix for filename: {$filename}");
                continue;
            }

            $this->comment("   Searching disk for orphaned files ending with '-{$suffix}.webp'...");

            // Search the media-library directory for files ending in -$suffix.webp
            $files = $disk->files('media-library');
            $matchedFiles = [];
            foreach ($files as $file) {
                $fileLower = strtolower($file);
                $suffixLower = strtolower($suffix);
                if (str_ends_with($fileLower, '-' . $suffixLower . '.webp')) {
                    $matchedFiles[] = $file;
                }
            }

            if (empty($matchedFiles)) {
                $this->error("   No orphaned files found for suffix: {$suffix}");
                continue;
            }

            // Take the first matching file and rename it back
            $orphanedFile = $matchedFiles[0];
            $this->info("   Found orphaned file: {$orphanedFile}. Renaming to {$cleanPath}...");

            try {
                $disk->move($orphanedFile, $cleanPath);
                $this->info("   [SUCCESS] Restored {$cleanPath}!");
                $healedCount++;
            } catch (\Exception $e) {
                $this->error("   Failed to move file: " . $e->getMessage());
            }
        }

        $this->info("\nHealing complete! Restored {$healedCount} files.");

        $this->info("\nScanning and updating Unit references...");
        $unitsUpdated = 0;
        $units = Unit::all();

        foreach ($units as $unit) {
            $blocks = $unit->content_blocks;
            if (!is_array($blocks)) {
                continue;
            }

            $changed = false;
            foreach ($blocks as $i => $block) {
                if (isset($block['type']) && $block['type'] === 'image' && isset($block['data'])) {
                    if (!empty($block['data']['media_item_selection'])) {
                        $mediaItem = MediaItem::find($block['data']['media_item_selection']);
                        if ($mediaItem) {
                            $cleanMediaItemPath = ltrim($mediaItem->path, '/');
                            if (str_starts_with($cleanMediaItemPath, 'storage/')) {
                                $cleanMediaItemPath = substr($cleanMediaItemPath, 8);
                            }

                            if (($block['data']['custom_url'] ?? '') !== $cleanMediaItemPath) {
                                $this->comment(" - Unit {$unit->id} ('{$unit->title}'): updating custom_url reference from '{$block['data']['custom_url']}' to '{$cleanMediaItemPath}'");
                                $blocks[$i]['data']['custom_url'] = $cleanMediaItemPath;
                                $changed = true;
                            }
                        }
                    }
                }
            }

            if ($changed) {
                $unit->content_blocks = $blocks;
                // With our safeguard in Unit model saving hook, this save will NOT rename/delete files in media-library/
                $unit->save();
                $unitsUpdated++;
            }
        }

        $this->info("Unit reference updates complete! Updated {$unitsUpdated} units.");
    }
}
