<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label(__('staff.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('staff.fields.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('staff_no')
                    ->label(__('staff.fields.staff_no'))
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                Select::make('site_id')
                    ->label(__('staff.fields.site'))
                    ->helperText(__('staff.fields.site_help'))
                    ->relationship('site', 'name', fn (Builder $query) => $query->whereNull('archived_at'))
                    ->searchable()
                    ->preload(),
                Select::make('role')
                    ->label(__('staff.fields.role'))
                    ->required()
                    ->options(self::roleOptions())
                    ->default(Role::Staff->value)
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Select $component, ?User $record): void {
                        if ($record?->primaryRole() !== null) {
                            $component->state($record->primaryRole()->value);
                        }
                    }),
                Select::make('locale')
                    ->label(__('staff.fields.locale'))
                    ->required()
                    ->options([
                        'en' => __('staff.locales.en'),
                        'ms' => __('staff.locales.ms'),
                    ])
                    ->default('en'),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function roleOptions(): array
    {
        $options = [];

        foreach (Role::cases() as $role) {
            $options[$role->value] = __('staff.roles.'.$role->value);
        }

        return $options;
    }
}
