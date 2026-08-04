<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Models\Unit;
use App\Models\CourseSession;
use App\Models\MediaItem;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Lesson Settings')
                    ->schema([
                        \Filament\Forms\Components\Select::make('course_session_id')
                            ->relationship(
                                name: 'courseSession',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn ($query) => $query->latest()
                            )
                            ->label('Part of Session')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                \Filament\Forms\Components\TextInput::make('title')
                                    ->required(),
                                \Filament\Forms\Components\Select::make('module_id')
                                    ->relationship('module', 'name')
                                    ->required()
                                    ->label('Module'),
                                \Filament\Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->hidden(), // Auto-generate if model observation works, or make visible to be safe
                            ])
                            ->required()
                            ->default(fn () => request()->query('session_id'))
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $maxOrder = \App\Models\Unit::where('course_session_id', $state)->max('sort_order');
                                    $nextOrder = ($maxOrder ?? 0) + 1;
                                    $set('sort_order', $nextOrder);
                                    $set('title', $nextOrder);
                                }
                            }),

                        \Filament\Forms\Components\TextInput::make('title')
                            ->required()
                            ->placeholder('e.g., Lesson 1: The Hook')
                            ->maxLength(255)
                            ->default(function ($get) {
                                $sessionId = $get('course_session_id') ?: request()->query('session_id');
                                if ($sessionId) {
                                    $maxOrder = \App\Models\Unit::where('course_session_id', $sessionId)->max('sort_order');
                                    return ($maxOrder ?? 0) + 1;
                                }
                                return 1;
                            }),

                        \Filament\Forms\Components\TextInput::make('sort_order')
                            ->label('Order')
                            ->numeric()
                            ->default(function ($get) {
                                $sessionId = $get('course_session_id') ?: request()->query('session_id');
                                if ($sessionId) {
                                    $maxOrder = \App\Models\Unit::where('course_session_id', $sessionId)->max('sort_order');
                                    return ($maxOrder ?? 0) + 1;
                                }
                                return 1;
                            }),

                        \Filament\Forms\Components\Toggle::make('is_free_sample')
                            ->label('Free Public Sample')
                            ->onColor('success'),

                        \Filament\Forms\Components\Toggle::make('is_registered_only')
                            ->label('Free for Registered Users')
                            ->onColor('warning')
                            ->helperText('Visible to any logged-in user (Free Tier)'),

                        \Filament\Forms\Components\Toggle::make('is_published')
                            ->label('Live for Students')
                            ->onColor('success')
                            ->default(true),

                        \Filament\Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('unit-thumbnails')
                            ->visibility('public')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Select::make('media_item_selection')
                            ->label('Select from Media Library')
                            ->options(fn () => MediaItem::where('type', 'image')->latest()->pluck('title', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                (($item = MediaItem::find($state)) ? [
                                    $set('custom_thumbnail_url', $item->path),
                                    $set('thumbnail', $item->path),
                                ] : null)
                            )
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\TextInput::make('custom_thumbnail_url')
                            ->label('OR Paste Thumbnail Link (or relative path)')
                            ->placeholder('e.g. media-library/image.jpg')
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($record) => $record?->thumbnail)
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('thumbnail', $state))
                            ->dehydrated(false),

                        \Filament\Forms\Components\FileUpload::make('audio_url')
                            ->label('Pronunciation Audio (MP3)')
                            ->disk('public')
                            ->directory('unit-audio')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav'])
                            ->columnSpanFull(),

                    ])->columns(2),



                \Filament\Schemas\Components\Section::make('nuWudy content builder')
                    ->schema([
                        \Filament\Forms\Components\Builder::make('content_blocks')
                            ->label('Lesson Content Designer')
                            ->blockPickerColumns(2)
                            ->collapsible()
                            ->blocks([
                                // 1. VIDEO BLOCK (Consolidated)
                                \Filament\Forms\Components\Builder\Block::make('video')
                                    ->label('Video Player')
                                    ->icon('heroicon-m-play-circle')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('instructions')
                                            ->label('Instructions (Optional)')
                                            ->placeholder('e.g., Watch the video below...'),
                                        \Filament\Forms\Components\TextInput::make('bunny_id')
                                            ->label('Bunny.net Video ID')
                                            ->placeholder('Paste Bunny.net ID here...'),
                                        \Filament\Forms\Components\FileUpload::make('video_path')
                                            ->label('OR Upload Video File')
                                            ->disk('public')
                                            ->directory('lesson-videos')
                                            ->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/webm']),
                                        \Filament\Forms\Components\Select::make('media_item_id')
                                            ->label('OR Select from Media Library')
                                            ->options(fn () => \App\Models\MediaItem::where('type', 'video')->latest()->pluck('title', 'id'))
                                            ->searchable(),
                                        \Filament\Forms\Components\Textarea::make('transcript')
                                            ->label('Transcript (Optional)')
                                            ->placeholder('Type the video transcript here...')
                                            ->rows(4),
                                        \Filament\Forms\Components\Textarea::make('meaning_malayalam')
                                            ->label('Meaning in Malayalam (Optional)')
                                            ->placeholder('e.g., ഹലോ, കോഴ്സിലേക്ക് സ്വാഗതം!')
                                            ->rows(3),
                                    ]),

                                // 2. IMAGE BLOCK
                                \Filament\Forms\Components\Builder\Block::make('image')
                                    ->label('Image Resource')
                                    ->icon('heroicon-m-photo')
                                    ->schema([
                                        \Filament\Forms\Components\FileUpload::make('url')
                                            ->label('Upload Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('lesson-images'),
                                        \Filament\Forms\Components\Select::make('media_item_selection')
                                            ->label('OR Select from Media Library')
                                            ->options(fn () => \App\Models\MediaItem::where('type', 'image')->latest()->pluck('title', 'id'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, callable $set) =>
                                                (($item = \App\Models\MediaItem::find($state)) ? $set('custom_url', $item->path) : null)
                                            ),
                                        \Filament\Forms\Components\TextInput::make('custom_url')
                                            ->label('OR Paste Image Link / Path'),
                                        \Filament\Forms\Components\TextInput::make('alt')
                                            ->label('Alt Text (Optional)'),
                                    ]),

                                // TEXT/HTML BLOCK
                                \Filament\Forms\Components\Builder\Block::make('rich_text')
                                    ->icon('heroicon-m-document-text')
                                    ->schema([
                                        \Filament\Forms\Components\Toggle::make('manglish_enabled')
                                            ->label('Type in Malayalam')
                                            ->live()
                                            ->dehydrated(false)
                                            ->columnSpanFull()
                                            ->extraAttributes([
                                                'class' => 'manglish-toggle-trigger'
                                            ]),
                                        \Filament\Forms\Components\RichEditor::make('content')
                                            ->label('Text or Custom HTML')
                                            ->toolbarButtons([
                                                'blockquote', 'bold', 'bulletList', 'codeBlock', 
                                                'h1', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'underline', 'undo'
                                            ]),
                                        \Filament\Forms\Components\Select::make('alignment')
                                            ->label('Text Alignment')
                                            ->options([
                                                'left' => 'Left Aligned',
                                                'center' => 'Centered',
                                                'right' => 'Right Aligned',
                                            ])
                                            ->default('left'),
                                        \Filament\Forms\Components\Select::make('text_color')
                                            ->label('Premium Text Color')
                                            ->options([
                                                'default' => 'Default (Slate)',
                                                'indigo' => 'Royal Indigo',
                                                'emerald' => 'Emerald Green',
                                                'rose' => 'Velvet Rose',
                                                'blue' => 'Deep Sea Blue',
                                            ])
                                            ->default('default'),
                                    ]),

                                // CUSTOM CODE BLOCK
                                \Filament\Forms\Components\Builder\Block::make('code')
                                    ->icon('heroicon-m-code-bracket')
                                    ->label('Custom HTML/CSS')
                                    ->schema([
                                        \Filament\Forms\Components\Textarea::make('code')
                                            ->label('Paste HTML Code Here')
                                            ->rows(10),
                                    ]),

                                // AUDIO BLOCK
                                \Filament\Forms\Components\Builder\Block::make('audio')
                                    ->icon('heroicon-m-microphone')
                                    ->label('Audio Player')
                                    ->schema([
                                        \Filament\Forms\Components\FileUpload::make('url')
                                            ->label('Upload Audio (MP3)')
                                            ->disk('public')
                                            ->directory('lesson-audio')
                                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/mp3']),
                                        \Filament\Forms\Components\Select::make('media_item_selection')
                                            ->label('Select from Media Library')
                                            ->options(fn () => MediaItem::where('type', 'audio')->latest()->pluck('title', 'id'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, callable $set) =>
                                                (($item = MediaItem::find($state)) ? [
                                                    $set('custom_url', $item->path),
                                                    $set('audio_title', $item->title)
                                                ] : null)
                                            )
                                            ->dehydrated(false),
                                        \Filament\Forms\Components\TextInput::make('audio_title')
                                            ->label('Audio Title')
                                            ->placeholder('e.g., Pronunciation Guide')
                                            ->columnSpanFull(),
                                        \Filament\Forms\Components\TextInput::make('custom_url')
                                            ->label('OR Paste Audio Link (or relative path)')
                                            ->placeholder('e.g. media-library/audio.mp3'),

                                        \Filament\Forms\Components\TextInput::make('transcript')
                                            ->label('Audio Text (Transcript)')
                                            ->placeholder('e.g., I have a dream that one day...')
                                            ->helperText('This text will appear inside the card for students to read while listening.'),
                                    ]),


                                // 6. QUIZ BLOCK
                                \Filament\Forms\Components\Builder\Block::make('quiz')
                                    ->label('Interactive Quiz')
                                    ->icon('heroicon-m-question-mark-circle')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('instructions')
                                            ->label('Instructions (Optional)')
                                            ->placeholder('e.g., Choose the correct word below...'),
                                        \Filament\Forms\Components\TextInput::make('question')
                                            ->label('Question / Prompt')
                                            ->required(),
                                        \Filament\Forms\Components\Repeater::make('options')
                                            ->label('Answer Options')
                                            ->schema([
                                                \Filament\Forms\Components\TextInput::make('text')
                                                    ->label('Option Text')
                                                    ->required(),
                                                \Filament\Forms\Components\Toggle::make('is_correct')
                                                    ->label('Correct Answer'),
                                                
                                                \Filament\Forms\Components\FileUpload::make('audio_path')
                                                    ->label('Upload Audio (MP3)')
                                                    ->disk('public')
                                                    ->directory('lesson-audio')
                                                    ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/mp3'])
                                                    ->columnSpanFull(),

                                                \Filament\Forms\Components\Select::make('media_item_selection')
                                                    ->label('OR Select from Media Library')
                                                    ->options(fn () => \App\Models\MediaItem::where('type', 'audio')->latest()->pluck('title', 'id'))
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateUpdated(fn ($state, callable $set) =>
                                                        (($item = \App\Models\MediaItem::find($state)) ? [
                                                            $set('custom_audio_url', $item->path),
                                                            $set('audio_title', $item->title)
                                                        ] : null)
                                                    )
                                                    ->dehydrated(false)
                                                    ->columnSpanFull(),

                                                \Filament\Forms\Components\TextInput::make('audio_title')
                                                    ->label('Audio Title')
                                                    ->placeholder('e.g., Explanation of Answer')
                                                    ->columnSpanFull(),

                                                \Filament\Forms\Components\TextInput::make('custom_audio_url')
                                                    ->label('OR Paste Audio Link (or relative path)')
                                                    ->placeholder('e.g. media-library/audio.mp3')
                                                    ->columnSpanFull(),

                                                \Filament\Forms\Components\Textarea::make('speech_text')
                                                    ->label('OR AI Speech Text (Read aloud upon correct answer)')
                                                    ->placeholder('e.g., Exactly! The present perfect tense is used here.')
                                                    ->rows(2)
                                                    ->columnSpanFull(),

                                                \Filament\Forms\Components\Select::make('speech_accent')
                                                    ->label('AI Speech Accent')
                                                    ->options([
                                                        'en-US' => 'American English (en-US)',
                                                        'en-GB' => 'British English (en-GB)',
                                                        'en-AU' => 'Australian English (en-AU)',
                                                    ])
                                                    ->default('en-US')
                                                    ->columnSpanFull(),

                                                \Filament\Forms\Components\Select::make('speech_gender')
                                                    ->label('Preferred Voice Gender')
                                                    ->options([
                                                        'any' => 'Any / Default',
                                                        'male' => 'Male',
                                                        'female' => 'Female',
                                                    ])
                                                    ->default('any')
                                                    ->columnSpanFull(),
                                            ])
                                            ->minItems(2)
                                            ->grid(2),
                                    ]),


                                // VOICE MATCH BLOCK
                                \Filament\Forms\Components\Builder\Block::make('voice_match')
                                    ->label('Voice Check (Green Light)')
                                    ->icon('heroicon-m-microphone')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('instructions')
                                            ->label('Instructions (Optional)')
                                            ->placeholder('e.g. Read the phrase below out loud...'),

                                        \Filament\Forms\Components\TagsInput::make('phrase')
                                            ->label('The Phrase to Match (Variations)')
                                            ->required()
                                            ->placeholder('Type and press Enter to add options')
                                            ->helperText('Add "I have a dream" AND "I have a big dream" to accept both.'),

                                        \Filament\Forms\Components\Textarea::make('meaning_malayalam')
                                            ->label('Meaning in Malayalam (Optional)')
                                            ->placeholder('e.g., ഹലോ, കോഴ്സിലേക്ക് സ്വാഗതം!')
                                            ->rows(3),

                                        \Filament\Forms\Components\TextInput::make('button_label')
                                            ->label('Button Text')
                                            ->default('Tap to Speak & Match'),

                                        \Filament\Forms\Components\Toggle::make('hide_phrase')
                                            ->label('Hide phrase from student by default')
                                            ->helperText('Students will see a blurred box and must tap to reveal.')
                                            ->default(true),
                                    ]),

                                // TEXT TO SPEECH (AI PRONUNCIATION) BLOCK
                                \Filament\Forms\Components\Builder\Block::make('text_to_speech')
                                    ->label('Text to Speech (AI Pronunciation)')
                                    ->icon('heroicon-m-speaker-wave')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('heading')
                                            ->label('Card Heading')
                                            ->default('AI Pronunciation Guide')
                                            ->placeholder('e.g., Practice Listening'),

                                        \Filament\Forms\Components\TextInput::make('instructions')
                                            ->label('Instructions (Optional)')
                                            ->placeholder('e.g., Listen to the pronunciation...'),

                                        \Filament\Forms\Components\Textarea::make('text')
                                            ->label('Default Text to Speak')
                                            ->placeholder('e.g., Hello, welcome to the course!')
                                            ->required()
                                            ->rows(4),

                                        \Filament\Forms\Components\Textarea::make('meaning_malayalam')
                                            ->label('Meaning in Malayalam (Optional)')
                                            ->placeholder('e.g., ഹലോ, കോഴ്സിലേക്ക് സ്വാഗതം!')
                                            ->rows(3),

                                        \Filament\Forms\Components\Select::make('speech_gender')
                                            ->label('Voice Gender')
                                            ->options([
                                                'any' => 'Any / Default',
                                                'male' => 'Male',
                                                'female' => 'Female',
                                            ])
                                            ->default('any'),

                                        \Filament\Forms\Components\Toggle::make('student_input_allowed')
                                            ->label('Allow Students to modify text')
                                            ->helperText('If enabled, students can type their own sentences directly to hear them pronounced.')
                                            ->default(false),

                                        \Filament\Forms\Components\Toggle::make('show_headings')
                                            ->label('Show headings for student')
                                            ->helperText('If enabled, the "Interactive Audio" label and heading will be displayed to students.')
                                            ->default(false),
                                    ]),

                                // DICTATION & LISTENING TEST BLOCK
                                \Filament\Forms\Components\Builder\Block::make('dictation_test')
                                    ->label('Dictation & Listening Test')
                                    ->icon('heroicon-m-check-badge')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('heading')
                                            ->label('Test Title')
                                            ->default('Dictation Test: Listen & Write')
                                            ->required(),

                                        \Filament\Forms\Components\Textarea::make('correct_sentence')
                                            ->label('The Correct Sentence (Exact Match)')
                                            ->placeholder('e.g., I would like to order a coffee, please.')
                                            ->required()
                                            ->rows(3),

                                        \Filament\Forms\Components\Select::make('speech_gender')
                                            ->label('Preferred Voice Gender')
                                            ->options([
                                                'any' => 'Any / Default',
                                                'male' => 'Male',
                                                'female' => 'Female',
                                            ])
                                            ->default('any'),

                                        \Filament\Forms\Components\TextInput::make('success_message')
                                            ->label('Success Feedback message')
                                            ->default('Great job! Your listening is spot on!')
                                            ->required(),
                                    ]),

                                // LISTEN AND SPEAK BLOCK
                                \Filament\Forms\Components\Builder\Block::make('listen_speak')
                                    ->label('Listen & Speak')
                                    ->icon('heroicon-m-sparkles')
                                    ->schema([
                                        \Filament\Forms\Components\Textarea::make('english_text')
                                            ->label('English Text')
                                            ->required()
                                            ->placeholder('e.g. I learn to speak english')
                                            ->rows(3),
                                            
                                        \Filament\Forms\Components\Toggle::make('hide_english')
                                            ->label('Hide English by default')
                                            ->helperText('Students must click a button to reveal the English text')
                                            ->default(false),
                                            
                                        \Filament\Forms\Components\Textarea::make('malayalam_text')
                                            ->label('Malayalam Translation (Optional)')
                                            ->placeholder('e.g. ഞാൻ ഇംഗ്ലീഷ് സംസാരിക്കാൻ പഠിക്കുന്നു')
                                            ->rows(3),
                                    ]),

                                // 8. SEPARATOR BLOCK
                                \Filament\Forms\Components\Builder\Block::make('separator')
                                    ->label('Space / Divider')
                                    ->icon('heroicon-m-minus')
                                    ->schema([
                                        \Filament\Forms\Components\Select::make('style')
                                            ->options([
                                                'empty_space' => 'Empty Space',
                                                'thin_line' => 'Thin Line',
                                                'bold_divider' => 'Bold Divider',
                                            ])
                                            ->default('empty_space'),
                                    ]),
                            ])
                            ->collapsible()
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->inset()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                // 1. RENDER THE CONTENT BLOCKS (Full Width)
                \Filament\Infolists\Components\ViewEntry::make('content_blocks')
                    ->label(false)
                    ->view('filament.components.unit-renderer-student')
                    ->viewData(fn ($record) => [
                        'blocks' => $record->content_blocks,
                        'unit' => $record,
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('courseSession.title')
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Preview')
                    ->rounded()
                    ->disk('public')
                    ->size(80)
                    ->getStateUsing(fn ($record) => $record->thumbnail ? asset('storage/' . $record->thumbnail) : null),

                TextColumn::make('sort_order')
                    ->label('#')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->description(fn ($record) => "Title: " . ($record->courseSession?->title ?? 'Unassigned')),
                
                IconColumn::make('is_free_sample')
                    ->boolean()
                    ->label('Public'),

                IconColumn::make('is_registered_only')
                    ->boolean()
                    ->label('Registered Only'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('course_session_id')
                    ->relationship(
                        name: 'courseSession',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn ($query) => $query->latest()
                    )
                    ->searchable()
                    ->preload()
                    ->label('Filter by Session')
                    ->default(fn () => request()->query('session_id') ?: null),
            ])
            ->actions([
                ViewAction::make()
                    ->url(function ($record, $livewire) {
                        $sessionId = $livewire->tableFilters['course_session_id']['value'] ?? request()->query('session_id');
                        return $sessionId ? static::getUrl('view', ['record' => $record]) . '?session_id=' . $sessionId : static::getUrl('view', ['record' => $record]);
                    }),
                EditAction::make()
                    ->url(function ($record, $livewire) {
                        $sessionId = $livewire->tableFilters['course_session_id']['value'] ?? request()->query('session_id');
                        return $sessionId ? static::getUrl('edit', ['record' => $record]) . '?session_id=' . $sessionId : static::getUrl('edit', ['record' => $record]);
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // Helper Logic for finding adjacent units
    protected static function getAdjacentUnit($record, $direction)
    {
        $query = \App\Models\Unit::where('course_session_id', $record->course_session_id)
            ->where('is_published', true);

        if ($direction === 'next') {
            return $query->where('sort_order', '>', $record->sort_order)->orderBy('sort_order', 'asc')->first();
        }
        
        return $query->where('sort_order', '<', $record->sort_order)->orderBy('sort_order', 'desc')->first();
    }

    protected static function getAdjacentUnitUrl($record, $direction)
    {
        $unit = self::getAdjacentUnit($record, $direction);
        return $unit ? UnitResource::getUrl('view', ['record' => $unit->id]) : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'view' => Pages\ViewUnit::route('/{record}'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}