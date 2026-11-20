<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\StaffStatus;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Notifications\StaffInvitation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

/**
 * Creating a Staff member invites them by email; they set their own password.
 */
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'password' => Str::password(64),
            'status' => StaffStatus::Invited,
        ];
    }

    protected function afterCreate(): void
    {
        /** @var User $staffMember */
        $staffMember = $this->record;

        $staffMember->syncRoles([$this->data['role']]);
        $staffMember->notify(new StaffInvitation);
    }
}
