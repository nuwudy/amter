<?php

namespace App\Filament\Resources\MediaItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->columnSpanFull(),
                \Filament\Forms\Components\Placeholder::make('media_preview')
                    ->label('Media Preview / Player')
                    ->visible(fn ($record) => $record && $record->path)
                    ->columnSpanFull()
                    ->content(function ($record) {
                        if (!$record || !$record->path) {
                            return null;
                        }

                        $url = asset('storage/' . $record->path);
                        $type = $record->type;

                        // Robust fallback detection if type column is empty/null
                        if (!$type) {
                            $ext = strtolower(pathinfo($record->path, PATHINFO_EXTENSION));
                            if (in_array($ext, ['mp4', 'mkv', 'webm', 'mov', 'avi'])) {
                                $type = 'video';
                            } elseif (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'mpga'])) {
                                $type = 'audio';
                            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                                $type = 'image';
                            }
                        }

                        if ($type === 'video') {
                            return new \Illuminate\Support\HtmlString("
                                <div class='flex flex-col items-center justify-center p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm'>
                                    <video controls class='w-full max-h-[360px] rounded-xl border border-slate-300 dark:border-slate-700 shadow-md' src='{$url}'></video>
                                    <div class='mt-3 text-xs text-slate-500 font-medium'>Playing from: <a href='{$url}' target='_blank' class='text-primary-600 underline hover:text-primary-500'>{$record->path}</a></div>
                                </div>
                            ");
                        } elseif ($type === 'audio') {
                            return new \Illuminate\Support\HtmlString("
                                <div class='flex flex-col items-center justify-center p-6 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm'>
                                    <audio controls class='w-full max-w-md' src='{$url}'></audio>
                                    <div class='mt-3 text-xs text-slate-500 font-medium'>Playing from: <a href='{$url}' target='_blank' class='text-primary-600 underline hover:text-primary-500'>{$record->path}</a></div>
                                </div>
                            ");
                        } elseif ($type === 'image') {
                            return new \Illuminate\Support\HtmlString("
                                <div class='flex flex-col items-center justify-center p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm'>
                                    <img class='max-h-[300px] rounded-xl border border-slate-300 dark:border-slate-700 shadow-md object-contain' src='{$url}' />
                                    <div class='mt-3 text-xs text-slate-500 font-medium'>Viewing: <a href='{$url}' target='_blank' class='text-primary-600 underline hover:text-primary-500'>{$record->path}</a></div>
                                </div>
                            ");
                        }
                        return null;
                    }),
                \Filament\Forms\Components\FileUpload::make('path')
                    ->label('File')
                    ->disk('public')
                    ->directory('media-library')
                    ->required()
                    ->columnSpanFull()
                    ->helperText('Upload an image, video, or audio file.')
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state) {
                            $file = is_array($state) ? reset($state) : $state;
                            try {
                                $disk = \Illuminate\Support\Facades\Storage::disk('public');
                                if ($disk->exists($file)) {
                                    $mime = $disk->mimeType($file);
                                    $size = $disk->size($file);
                                    $set('mime_type', $mime);
                                    $set('size', $size);

                                    if ($mime) {
                                        if (str_starts_with($mime, 'image/')) {
                                            $set('type', 'image');
                                        } elseif (str_starts_with($mime, 'video/')) {
                                            $set('type', 'video');
                                        } elseif (str_starts_with($mime, 'audio/')) {
                                            $set('type', 'audio');
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                // Silent fallback
                            }
                        }
                    }),
                \Filament\Forms\Components\Select::make('type')
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video',
                        'audio' => 'Audio',
                    ])
                    ->required()
                    ->default('image'),
                TextInput::make('mime_type')
                    ->readOnly(),
                TextInput::make('size')
                    ->numeric()
                    ->readOnly()
                    ->hidden(),
            ]);
    }
}
