<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Url;

class CreateUnit extends CreateRecord
{
    protected static string $resource = UnitResource::class;

    #[Url]
    public ?string $session_id = null;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            '/admin' => 'Dashboard',
            UnitResource::getUrl('index') => 'All Courses',
        ];

        if ($this->session_id) {
            $session = \App\Models\CourseSession::find($this->session_id);
            if ($session) {
                $sessionUrl = UnitResource::getUrl('index') . '?tableFilters[course_session_id][value]=' . $this->session_id;
                $breadcrumbs[$sessionUrl] = $session->title;
            }
        }

        $breadcrumbs['#'] = 'Create';

        return $breadcrumbs;
    }
}
