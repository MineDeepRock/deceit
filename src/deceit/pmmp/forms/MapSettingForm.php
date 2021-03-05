<?php


namespace deceit\pmmp\forms;


use deceit\models\Map;
use deceit\pmmp\slot_menus\ExitVectorSettingSlotMenu;
use deceit\pmmp\slot_menus\InitialSpawnVectorSettingSlotMenu;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use slot_menu_system\SlotMenuSystem;

class MapSettingForm extends SimpleForm
{

    public function __construct(Map $map) {
        $buttons = [
            new SimpleFormButton(
                "名前の変更",
                null,
                function (Player $player) use ($map) {
                    $player->sendForm(new RenameMapForm($map));
                }
            ),
            new SimpleFormButton(
                "初期位置の変更",
                null,
                function (Player $player) use ($map) {
                    SlotMenuSystem::send($player, new InitialSpawnVectorSettingSlotMenu($map));
                }
            ),
            new SimpleFormButton(
                "出口の変更",
                null,
                function (Player $player) use ($map) {
                    SlotMenuSystem::send($player, new ExitVectorSettingSlotMenu($map));
                }
            ),
            new SimpleFormButton(
                "燃料タンクの変更",
                null,
                function (Player $player) use ($map) {
                    $player->sendForm(new FuelTankListForm($map));
                }
            ),
            new SimpleFormButton(
                "燃料のスポーン位置の変更",
                null,
                function (Player $player) use ($map) {
                    $player->sendForm(new FuelSpawnVectorSettingForm($map));
                }
            ),
            new SimpleFormButton(
                "銃のスポーン位置の変更",
                null,
                function (Player $player) use ($map) {
                    $player->sendForm(new GunDataOnMapListForm($map));
                }
            ),
            new SimpleFormButton(
                TextFormat::RED . "マップの削除",
                null,
                function (Player $player) use ($map) {
                    $player->sendForm(new ConfirmDeletingMapForm($map));
                }
            ),
        ];
        parent::__construct($map->getName(), "", $buttons);
    }

    function onClickCloseButton(Player $player): void {
    }
}