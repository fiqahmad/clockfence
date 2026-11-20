<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\StaffStatus;
use App\Models\User;
use App\Notifications\StaffInvitation;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('staff.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('staff_no')
                    ->label(__('staff.fields.staff_no'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('staff.fields.email'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('site.name')
                    ->label(__('staff.fields.site'))
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label(__('staff.fields.role'))
                    ->formatStateUsing(fn (string $state): string => __('staff.roles.'.$state)),
                TextColumn::make('status')
                    ->label(__('staff.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (StaffStatus $state): string => $state->label())
                    ->color(fn (StaffStatus $state): string => match ($state) {
                        StaffStatus::Active => 'success',
                        StaffStatus::Invited => 'warning',
                        StaffStatus::Inactive => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('staff.fields.status'))
                    ->options(collect(StaffStatus::cases())->mapWithKeys(
                        fn (StaffStatus $status): array => [$status->value => $status->label()],
                    )),
                SelectFilter::make('site')
                    ->label(__('staff.fields.site'))
                    ->relationship('site', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('resendInvitation')
                    ->label(__('staff.actions.resend_invitation'))
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->visible(fn (User $record): bool => $record->status === StaffStatus::Invited)
                    ->action(function (User $record): void {
                        $record->notify(new StaffInvitation);
                        Notification::make()->success()->title(__('staff.actions.invitation_sent'))->send();
                    }),
                Action::make('deactivate')
                    ->label(__('staff.actions.deactivate'))
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(__('staff.actions.deactivate_confirm'))
                    ->visible(fn (User $record): bool => $record->status !== StaffStatus::Inactive
                        && (auth()->user()?->can('deactivate', $record) ?? false))
                    ->action(function (User $record): void {
                        $record->deactivate();
                        Notification::make()->success()->title(__('staff.actions.deactivated'))->send();
                    }),
                Action::make('reactivate')
                    ->label(__('staff.actions.reactivate'))
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->visible(fn (User $record): bool => $record->status === StaffStatus::Inactive)
                    ->action(function (User $record): void {
                        $record->reactivate();
                        Notification::make()->success()->title(__('staff.actions.reactivated'))->send();
                    }),
            ]);
    }
}
