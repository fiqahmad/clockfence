<?php

namespace App\Filament\Resources\Sites\Tables;

use App\Models\Site;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('staffMembers'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('sites.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('sites.fields.start_time'))
                    ->time('H:i'),
                TextColumn::make('timezone')
                    ->label(__('sites.fields.timezone')),
                TextColumn::make('staff_members_count')
                    ->label(__('sites.fields.staff_count'))
                    ->sortable(),
                IconColumn::make('archived_at')
                    ->label(__('sites.fields.archived'))
                    ->boolean()
                    ->getStateUsing(fn (Site $record): bool => $record->isArchived()),
            ])
            ->filters([
                TernaryFilter::make('archived')
                    ->label(__('sites.filters.archived'))
                    ->nullable()
                    ->attribute('archived_at'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('poster')
                    ->label(__('sites.actions.poster'))
                    ->icon(Heroicon::OutlinedQrCode)
                    ->url(fn (Site $record): string => route('sites.poster', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Site $record): bool => ! $record->isArchived()),
                Action::make('archive')
                    ->label(__('sites.actions.archive'))
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(__('sites.actions.archive_confirm'))
                    ->visible(fn (Site $record): bool => ! $record->isArchived() && (auth()->user()?->can('archive', $record) ?? false))
                    ->action(function (Site $record): void {
                        if (! $record->canBeArchived()) {
                            Notification::make()->danger()->title(__('sites.actions.cannot_archive'))->send();

                            return;
                        }

                        $record->archive();
                        Notification::make()->success()->title(__('sites.actions.archived'))->send();
                    }),
                Action::make('unarchive')
                    ->label(__('sites.actions.unarchive'))
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->visible(fn (Site $record): bool => $record->isArchived() && (auth()->user()?->can('archive', $record) ?? false))
                    ->action(function (Site $record): void {
                        $record->unarchive();
                        Notification::make()->success()->title(__('sites.actions.unarchived'))->send();
                    }),
            ]);
    }
}
