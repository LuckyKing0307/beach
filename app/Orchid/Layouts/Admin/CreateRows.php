<?php

namespace App\Orchid\Layouts\Admin;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class CreateRows extends Rows
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
            Input::make('trigger.name')->required(1)->title('Name'),
            Input::make('trigger.function')->title('Function'),
            Input::make('trigger.trigger_en')->title('Trigger En')->required(1),
            Input::make('trigger.trigger_bg')->title('Trigger Bg')->required(1),
            Input::make('trigger.text_bg')->title('Text Bg')->required(1),
            Input::make('trigger.text_en')->title('Text En')->required(1),
            Matrix::make('trigger.fields')->title('Return Fields')
                ->columns([
                    'bg',
                    'en',
                ]),
        ];
    }
}
