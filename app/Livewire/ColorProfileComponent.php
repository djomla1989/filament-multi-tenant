<?php

namespace App\Livewire;

use Filament\Forms\Components\{ColorPicker, Section};
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Joaopaulolndev\FilamentEditProfile\Concerns\{HasSort, HasUser};
use Livewire\Component;

class ColorProfileComponent extends Component implements HasForms
{
    use InteractsWithForms;
    use HasSort;
    use HasUser;

    public ?array $data = [];

    public $userClass;

    protected static int $sort = 30;

    public function mount(): void
    {
        $this->user      = $this->getUser();
        $this->userClass = get_class($this->user);
        $this->form->fill($this->user->only('settings'));

    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Theme Color')
                    ->aside()
                    ->description('Choose the theme color')
                    ->schema([

                        ColorPicker::make('settings.color')
                            ->label('Theme Color')
                            ->columnSpanFull()
                            ->inLineLabel()
                            ->default('#f59e0b'),
                    ]),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $this->user->update($data);

        redirect((request()->header('referer')));
    }

    protected function afterSave(): void
    {
        redirect((request()->header('referer')));
    }

    public function render(): View
    {
        return view('livewire.color-profile-component');
    }
}
