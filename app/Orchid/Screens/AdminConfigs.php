<?php

namespace App\Orchid\Screens;

use App\Models\TelegramUser as TelegramUserModel;
use App\Orchid\Layouts\Admin\ConfigsTable;
use App\Orchid\Layouts\Admin\CreateRows;
use App\Orchid\Layouts\Admin\EditConfigRows;
use App\Orchid\Layouts\Admin\EditItemConfigRows;
use Illuminate\Http\Request;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Screen;
use App\Models\AdminConfigs as AdConfigs;
use Orchid\Support\Facades\Layout;

class AdminConfigs extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'configs' => AdConfigs::orderBy('type')->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Admin Configs';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Trigger')
                ->modal('createTrigger')
                ->method('create')
                ->icon('plus'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            ConfigsTable::class,
            Layout::modal('createTrigger', CreateRows::class)
                ->title('Create Trigger')
                ->applyButton('Add Trigger'),
            Layout::modal('editConfig', EditConfigRows::class)->async('asyncGetConfig'),
            Layout::modal('editItemConfig', EditItemConfigRows::class)->async('asyncGetConfig')
        ];
    }

    public function asyncGetConfig(AdConfigs $config): array
    {
        $data = json_decode($config->data, 1);
        if ($config->type === 'section_item') {
            $configDb = [
                'id' => $config->id,
                'type' => $config->type,
                'name' => $config->name,
                'item_text_en' => isset($data['text']) ? $data['text']['en'] : '',
                'item_text_bg' => isset($data['text']) ? $data['text']['bg'] : '',
                'price' => isset($data['price']) ? $data['price'] : '',
            ];
        } else {
            $configDb = [
                'id' => $config->id,
                'type' => $config->type,
                'name' => $config->name,
                'text_en' => isset($data['text']) ? $data['text']['en'] : '',
                'text_bg' => isset($data['text']) ? $data['text']['bg'] : '',
                'fields' => isset($data['fields']) ? $data['fields'] : [],
            ];
        }
        return [
            'trigger' => $configDb
        ];
    }

    /**
     *
     * @return void
     */
    public function create(Request $request)
    {
        // Validate form data, save task to database, etc.
        $request->validate([
            'trigger.name' => 'required',
            'trigger.function' => 'required',
            'trigger.trigger_en' => 'required',
            'trigger.trigger_bg' => 'required',
            'trigger.fields' => 'required',
        ]);
//        dd($request->all());
        $task = new AdConfigs();
        $fields = [];
        foreach ($request->input('trigger.fields') as $trigger) {
            $sections = AdConfigs::create([
                'name' => $trigger['en'],
                'function' => 'sectionData',
                'trigger_en' => $trigger['en'],
                'trigger_bg' => $trigger['bg'],
                'type' => 'section',
                'fields' => '[]',
                'data' => '[]',
            ]);

            $fields[$sections->id] = $trigger;
        }
        $data = [
            'text' => ['en' => $request->input('trigger.text_en'), 'bg' => $request->input('trigger.text_bg')],
            'fields' => $fields,
        ];
        $task->name = $request->input('trigger.name');
        $task->type = 'menu';
        $task->function = $request->input('trigger.function');
        $task->trigger_en = $request->input('trigger.trigger_en');
        $task->trigger_bg = $request->input('trigger.trigger_bg');
        $task->data = json_encode($data);
        $task->save();
    }

    public function edit(Request $request)
    {
        $config = AdConfigs::find($request->input('trigger.id'));
        if ($config->type === 'menu') {
            $this->changeMenuSection($request, $config);
        }
        if ($config->type === 'section_item') {
            $request->validate([
                'trigger.item_text_en' => 'required',
                'trigger.item_text_bg' => 'required',
                'trigger.price' => 'required',
            ]);
            $this->changeSectionItem($request, $config);
        } else {
            $request->validate([
                'trigger.text_en' => 'required',
                'trigger.text_bg' => 'required',
                'trigger.fields' => 'required',
            ]);
            $this->changeSection($request, $config);
        }
    }

    public function changeSectionItem(Request $request, AdConfigs $config)
    {
        $photoUrl = [];
        $photoIds = $request->input('trigger.photo', []) ? $request->input('trigger.photo', []) : [];
        foreach ($photoIds as $photoId) {
            $attachment = Attachment::find($photoId);
            if ($attachment) {
                $photoUrl[] = $attachment->id;
            }
        }

        $data = [
            'text' => ['en' => $request->input('trigger.item_text_en'), 'bg' => $request->input('trigger.item_text_bg')],
            'price' => $request->input('trigger.price'),
        ];
        $config->data = json_encode($data);
        $config->function = $photoUrl;
        $config->save();

    }


    public function changeSection(Request $request, $config)
    {

        $fields = [];

        foreach ($request->input('trigger.fields') as $trigger) {
            $configGet = AdConfigs::where(['name' => $trigger['en']]);
            if (!$configGet->exists()) {
                $data = [];
                $sections = AdConfigs::create([
                    'name' => $trigger['en'],
                    'function' => '',
                    'trigger_en' => $trigger['en'],
                    'trigger_bg' => $trigger['bg'],
                    'type' => 'section',
                    'fields' => '[]',
                    'data' => json_encode($data),
                ]);
            } else {
                $sections = $configGet->get()->first();
                $sections->update([
                    'trigger_en' => $trigger['en'],
                    'trigger_bg' => $trigger['bg'],
                ]);
            }
            $fields[$sections->id] = $trigger;

        }
        $data = [
            'text' => ['en' => $request->input('trigger.text_en'), 'bg' => $request->input('trigger.text_bg')],
            'fields' => $fields,
        ];
        $config->data = json_encode($data);
        $config->save();
    }

    public function changeMenuSection(Request $request, $config)
    {
        $currentData = json_decode($config->data, true) ?? [];
        $storedFields = $currentData['fields'] ?? [];
        $incomings = $request->input('trigger.fields');
        $updatedFields = [];

        foreach ($incomings as $key => $incoming) {
                $t = $incoming;
                $field = AdConfigs::where('id',$key);
                if (isset($storedFields[$key]) and $field->exists()) {
                    $id = $key;
                    AdConfigs::find($key)->update([
                        'name' => $t['en'],
                        'trigger_en' => $t['en'],
                        'trigger_bg' => $t['bg'],
                        'function' => 'sectionData',
                    ]);
                } else {
                    $section = AdConfigs::create([
                        'name' => $t['en'],
                        'function' => 'sectionData',
                        'trigger_en' => $t['en'],
                        'trigger_bg' => $t['bg'],
                        'type' => 'section',
                        'fields' => '[]',
                        'data' => json_encode([]),
                    ]);
                    $id = $section->id;
                }

                $updatedFields[$id] = ['en' => $t['en'], 'bg' => $t['bg']];
        }
        $currentData['text'] = [
            'en' => $request->input('trigger.text_en'),
            'bg' => $request->input('trigger.text_bg'),
        ];
        $currentData['fields'] = $updatedFields;

        $config->data = json_encode($currentData, JSON_UNESCAPED_UNICODE);
        $config->save();
    }

}
