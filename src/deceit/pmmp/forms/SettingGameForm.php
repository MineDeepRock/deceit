<?php


namespace deceit\pmmp\forms;


use deceit\pmmp\services\StartGamePMMPService;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;

class SettingGameForm extends SimpleForm
{

    public function __construct() {
        parent::__construct("試合の設定", "", [
            new SimpleFormButton("開始", null, function (Player $player) {
                StartGamePMMPService::execute($player);
            })
        ]);
    }

    function onClickCloseButton(Player $player): void {
    }
}