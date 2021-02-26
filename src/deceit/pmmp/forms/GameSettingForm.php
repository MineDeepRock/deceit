<?php


namespace deceit\pmmp\forms;


use deceit\pmmp\services\StartGamePMMPService;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;

class GameSettingForm extends SimpleForm
{

    public function __construct() {
        parent::__construct("試合の設定", "", [
            new SimpleFormButton("開始", null, function (Player $player) {
                $result = StartGamePMMPService::execute($player);
                if (!$result) {
                    $player->sendMessage("試合を開始できませんでした");
                }
            })
        ]);
    }

    function onClickCloseButton(Player $player): void {
    }
}