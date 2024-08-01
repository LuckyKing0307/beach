<?php

namespace App\Orchid\Layouts\Admin;

use App\Models\AdminConfigs;
use App\Models\TelegramUser;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ConfigsTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'configs';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('name', 'ID'),
            TD::make('function', 'Function')->render(function (AdminConfigs $config) {
//                if (!strlen($config->function)<5){
////                    $photoLink = str_replace('//','/',$config->attachment()->first()?->getRelativeUrlAttribute());
////                    return "<img src='{$photoLink}' height='50'>";
//                    return $config->function;
//                }
                return $config->function;
            }),
            TD::make('type', 'Type')->render(function ($confid){
                if ($confid->type == 'menu') {
                    return 'Main menu';
                }
                if ($confid->type == 'section') {
                    return 'Section';
                }
                if ($confid->type == 'section_item') {
                    return 'Items';
                }
            }),
            TD::make('trigger_en', 'Trigger')->render(function($config) {
                $text = '';
                $text.="BG: {$config->trigger_bg}   EN: {$config->trigger_en}";
                return $text;
            }),
            TD::make('Show information')->render(function (AdminConfigs $config) {
                if ($config->type==='section_item'){
                    return ModalToggle::make('Edit Product')
                        ->modal('editConfig')
                        ->method('edit')
                        ->asyncParameters([
                            'config' => $config->id
                        ]);
                }else{
                    return ModalToggle::make('Edit Menu')
                        ->modal('editItemConfig')
                        ->method('edit')
                        ->asyncParameters([
                            'config' => $config->id
                        ]);
                }
            }),
        ];
    }
}
