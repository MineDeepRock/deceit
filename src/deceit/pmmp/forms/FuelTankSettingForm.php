<?php


namespace deceit\pmmp\forms;


use deceit\models\Map;
use deceit\pmmp\slot_menus\AddFuelTankSlotMenu;
use deceit\pmmp\slot_menus\FuelTankVectorSettingSlotMenu;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;
use slot_menu_system\SlotMenuSystem;

class FuelTankSettingForm extends SimpleForm
{
    private Map $map;

    public function __construct(Map $map) {
        $this->map = $map;
        $buttons = [
            new SimpleFormButton(
                "タンクを追加",
                null,
                function (Player $player) use ($map) {
                    SlotMenuSystem::send($player, new AddFuelTankSlotMenu($map));
                }
            )
        ];

        foreach ($map->getFuelTankVectors() as $vector) {
            $buttons[] = new SimpleFormButton(
                "x:{$vector->getY()},y:{$vector->getY()},z:{$vector->getZ()}",
                null,
                function (Player $player) use ($map, $vector) {
                    $player->teleport($vector);
                    SlotMenuSystem::send($player, new FuelTankVectorSettingSlotMenu($map, $vector));
                }
            );
        }

        parent::__construct($map->getName() . "のタンクの設定", "", $buttons);
    }

    function onClickCloseButton(Player $player): void {
        $player->sendForm(new MapSettingForm($this->map));
    }
}