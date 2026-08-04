<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\Url;

class EditUnit extends EditRecord
{
    protected static string $resource = UnitResource::class;

    #[Url]
    public ?string $session_id = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            '/admin' => 'Dashboard',
            UnitResource::getUrl('index') => 'All Courses',
        ];

        if ($this->record && $this->record->courseSession) {
            $sessionUrl = UnitResource::getUrl('index') . '?tableFilters[course_session_id][value]=' . $this->record->course_session_id;
            $breadcrumbs[$sessionUrl] = $this->record->courseSession->title;
        }

        $breadcrumbs['#'] = ($this->record->title ?? 'Edit') . ' (Edit)';

        return $breadcrumbs;
    }

}
