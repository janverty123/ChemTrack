<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\StudentAccounts;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            if (filled($data['category'] ?? null) && ! $record->student_code) {
                $record = app(StudentAccounts::class)->assignCode(auth()->user(), $record, $data['category']);
            }
            unset($data['category'], $data['student_code']);

            return parent::handleRecordUpdate($record, $data);
        }, 5);
    }
}
