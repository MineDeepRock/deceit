<?php


namespace deceit\pmmp\forms;


use deceit\models\Map;
use deceit\pmmp\slot_menus\AddItemDataOnMapSlotMenu;
use deceit\pmmp\slot_menus\ItemDataOnMapSettingSlotMenu;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;
use slot_menu_system\SlotMenuSystem;

class ItemDataOnMapListForm extends SimpleForm
{
    private Map $map;

    public function __construct(Map $map) {
        $this->map = $map;
        $buttons = [
            new SimpleFormButton(
                "アイテムのスポーン地点を追加",
                null,
                function (Player $player) use ($map) {
                    SlotMenuSystem::send($player, new AddItemDataOnMapSlotMenu($map));
                }
            )
        ];

        foreach ($map->getItemDataOnMapList() as $itemDataOnMap) {
            $vector = $itemDataOnMap->getVector();

            $buttons[] = new SimpleFormButton(
                "アイテム名:{$itemDataOnMap->getName()},x:{$vector->getY()},y:{$vector->getY()},z:{$vector->getZ()}",
                null,
                function (Player $player) use ($map, $itemDataOnMap) {
                    $player->teleport($itemDataOnMap->getVector());
                    SlotMenuSystem::send($player, new ItemDataOnMapSettingSlotMenu($map, $itemDataOnMap));
                }
            );
        }

        parent::__construct($map->getName() . "アイテムのスポーン地点を追加", "", $buttons);
    }

    function onClickCloseButton(Player $player): void {
        $player->sendForm(new MapSettingForm($this->map));
    }
}