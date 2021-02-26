<?php


namespace deceit\pmmp\forms;


use deceit\models\Map;
use deceit\pmmp\slot_menus\AddFuelSpawnVectorSlotMenu;
use deceit\pmmp\slot_menus\FuelVectorSettingSlotMenu;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;
use slot_menu_system\SlotMenuSystem;

class FuelSpawnVectorSettingForm extends SimpleForm
{

    public function __construct(Map $map) {
        $buttons = [
            new SimpleFormButton(
                "スポーン地点を追加",
                null,
                function (Player $player) use ($map) {
                    SlotMenuSystem::send($player, new AddFuelSpawnVectorSlotMenu($map));
                }
            )
        ];

        foreach ($map->getFuelSpawnVectors() as $vector) {
            $buttons[] = new SimpleFormButton(
                "x:{$vector->getY()},y:{$vector->getY()},z:{$vector->getZ()}",
                null,
                function (Player $player) use ($map, $vector) {
                    $player->teleport($vector);
                    SlotMenuSystem::send($player, new FuelVectorSettingSlotMenu($map, $vector));
                }
            );
        }

        parent::__construct($map->getName() . "の燃料のスポーン位置の変更", "", $buttons);
    }

    function onClickCloseButton(Player $player): void {
    }
}