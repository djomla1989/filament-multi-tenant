<?php

namespace App\Filament\App\Resources\WhatsappInstanceResource\RelationManagers;

use App\Enums\Evolution\Typebot\{TriggerOperatorEnum, TriggerTypeEnum};
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\{Section, Select, TextInput};
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\{TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\{Tables};

class InstanceTypebotsRelationManager extends RelationManager
{
    protected static string $relationship = 'InstanceTypebots';

    protected static ?string $modelLabel = 'TypeBot Bot';

    protected static ?string $modelLabelPlural = "TypeBots";

    protected static ?string $title = 'TypeBot Bots';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Typebot Data')
                    ->schema([

                        TextInput::make('name')
                            ->label('Bot Description')
                            ->prefixIcon('fas-id-card')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('url')
                            ->label('Typebot URL')
                            ->prefix('https://')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('type_bot')
                            ->prefixIcon('fas-robot')
                            ->label('Typebot')
                            ->required()
                            ->maxLength(255),

                    ])->columns(3),

                Section::make('Trigger Data')
                    ->schema([

                        Select::make('trigger_type')
                            ->label('Trigger Type')
                            ->required()
                            ->reactive()
                            ->live()
                            ->options(TriggerTypeEnum::class),

                        Select::make('trigger_operator')
                            ->hidden(fn ($get) => $get('trigger_type') != 'keyword')
                            ->required()
                            ->reactive()
                            ->label('Trigger Operator')
                            ->options(TriggerOperatorEnum::class),

                        TextInput::make('trigger_value')
                            ->hidden(fn ($get) => !in_array($get('trigger_type'), ['advanced', 'keyword']))
                            ->label('Trigger Value')
                            ->prefixIcon('fas-keyboard')
                            ->reactive()
                            ->required()
                            ->maxLength(255),

                    ])->columns(3),

                Section::make('General Settings')
                ->schema([

                    TextInput::make('expire')
                        ->label('Expire in minutes')
                        ->prefixIcon('fas-clock')
                        ->numeric()
                        ->required(),

                    TextInput::make('keyword_finish')
                        ->label('Finish Keyword')
                        ->prefixIcon('fas-arrow-right-from-bracket')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('delay_message')
                        ->label('Default Delay (Milliseconds)')
                        ->prefixIcon('fas-clock')
                        ->required()
                        ->numeric(),

                    TextInput::make('unknown_message')
                        ->label('Unknown Message')
                        ->prefixIcon('fas-question')
                        ->required()
                        ->maxLength(30),

                    TextInput::make('debounce_time')
                        ->label('Debounce Time')
                        ->prefixIcon('fas-clock')
                        ->required()
                        ->numeric(),

                ])->columns(3),

                Section::make('General Options')
                ->schema([

                    ToggleButtons::make('listening_from_me')
                        ->label('Listening from me')
                        ->inline()
                        ->boolean()
                        ->required(),

                    ToggleButtons::make('stop_bot_from_me')
                        ->label('Stop bot by me')
                        ->inline()
                        ->boolean()
                        ->required(),

                    ToggleButtons::make('keep_open')
                        ->label('Keep open')
                        ->inline()
                        ->boolean()
                        ->required(),

                ])->columns(3),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Description'),

                TextColumn::make('url')
                    ->label('URL'),

                TextColumn::make('type_bot')
                    ->label('Typebot Code'),

                TextColumn::make('id_typebot')
                    ->label('Bot ID'),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
