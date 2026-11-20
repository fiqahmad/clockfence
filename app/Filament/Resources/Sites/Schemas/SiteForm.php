<?php

namespace App\Filament\Resources\Sites\Schemas;

use App\Models\Site;
use DateTimeZone;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('sites.sections.details'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('sites.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label(__('sites.fields.address'))
                            ->rows(3),
                    ]),
                Section::make(__('sites.sections.fence'))
                    ->description(__('sites.sections.fence_help'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('geofence_lat')
                            ->label(__('sites.fields.geofence_lat'))
                            ->required()
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90),
                        TextInput::make('geofence_lng')
                            ->label(__('sites.fields.geofence_lng'))
                            ->required()
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180),
                        TextInput::make('geofence_radius_m')
                            ->label(__('sites.fields.geofence_radius_m'))
                            ->required()
                            ->integer()
                            ->minValue(config('clockfence.min_radius_m'))
                            ->maxValue(config('clockfence.max_radius_m'))
                            ->default(config('clockfence.default_radius_m')),
                    ]),
                Section::make(__('sites.sections.schedule'))
                    ->columns(2)
                    ->schema([
                        TimePicker::make('start_time')
                            ->label(__('sites.fields.start_time'))
                            ->required()
                            ->seconds(false),
                        Select::make('timezone')
                            ->label(__('sites.fields.timezone'))
                            ->required()
                            ->searchable()
                            ->options(fn (): array => array_combine(
                                DateTimeZone::listIdentifiers(),
                                DateTimeZone::listIdentifiers(),
                            ))
                            ->in(DateTimeZone::listIdentifiers()),
                        CheckboxList::make('working_days')
                            ->label(__('sites.fields.working_days'))
                            ->required()
                            ->columns(4)
                            ->columnSpanFull()
                            ->options(self::weekdayOptions())
                            ->default(Site::DEFAULT_WORKING_DAYS)
                            ->dehydrateStateUsing(fn (array $state): array => array_map('intval', array_values($state))),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    public static function weekdayOptions(): array
    {
        $options = [];

        foreach (range(1, 7) as $isoWeekday) {
            $options[$isoWeekday] = __("sites.weekdays.{$isoWeekday}");
        }

        return $options;
    }
}
