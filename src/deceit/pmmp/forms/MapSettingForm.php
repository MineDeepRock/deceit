<?php


namespace deceit\pmmp\forms;


use deceit\models\Map;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;

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
                    //TODO:実装
                }
            ),
            new SimpleFormButton(
                "出口の変更",
                null,
                function (Player $player) use ($map) {
                    //TODO:実装
                }
            ),
            new SimpleFormButton(
                "燃料タンクの変更",
                null,
                function (Player $player) use ($map) {
                    //TODO:実装
                }
            ),
            new SimpleFormButton(
                "燃料のスポーン位置の変更",
                null,
                function (Player $player) use ($map) {
                    //TODO:実装
                }
            ),
        ];
        parent::__construct($map->getName(), "", $buttons);
    }

    function onClickCloseButton(Player $player): void {
    }
}