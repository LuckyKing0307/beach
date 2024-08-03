<?php

namespace App\Orchid\Layouts\Admin;

use App\Orchid\Screens\AdminConfigs;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Layouts\Rows;

class EditItemConfigRows extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Input::make('trigger.id')->hidden(1),
            Input::make('trigger.name')
                ->title('Trigger Name'),
            Input::make('trigger.trigger_en')
                ->title('Trigger En Name'),
            Input::make('trigger.trigger_bg')
                ->title('Trigger Bg Name'),
            Input::make('trigger.text_en')->title('Text En'),
            Input::make('trigger.text_bg')->title('Text Bg'),
            Matrix::make('trigger.fields')->title('Return Fields')
                ->columns(['bg', 'en',]),
        ];
    }
}
