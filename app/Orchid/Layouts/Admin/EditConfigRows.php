<?php

namespace App\Orchid\Layouts\Admin;

use App\Orchid\Screens\AdminConfigs;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;

class EditConfigRows extends Rows
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
        if ($this->query->get('trigger.type') == 'section'){
            return [
                Input::make('trigger.id')->hidden(1),
                Input::make('trigger.name')
                    ->title('Trigger Name')->disabled(1),
                Input::make('trigger.text_en')->title('Text En'),
                Input::make('trigger.text_bg')->title('Text Bg'),
                Matrix::make('trigger.fields')->title('Return Fields')
                    ->columns(['bg', 'en',]),
            ];
        }else{
            return [
                Input::make('trigger.id')->hidden(1),
                Input::make('trigger.name')
                    ->title('Trigger Name')->disabled(1),
                Input::make('trigger.item_text_en')->title('Text En'),
                Input::make('trigger.item_text_bg')->title('Text Bg'),
                Upload::make('trigger.photo')
                    ->title('Upload Photo')
                    ->acceptedFiles('image/*')
                    ->maxFiles(5),
                Matrix::make('trigger.price')->title('Item Price')
                    ->columns(['time', 'price',]),
            ];
        }
    }
}
