<?php


namespace deceit\pmmp\forms;


use deceit\utilities\GetWorldNameList;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;
use pocketmine\Server;

class SelectMapWorldForm extends SimpleForm
{

    public function __construct() {
        $levelNames = GetWorldNameList::execute();
        $buttons = [];
        foreach ($levelNames as $levelName) {
            $level = Server::getInstance()->getLevelByName($levelName);
            $buttons[] = new SimpleFormButton("", null, function (Player $player) use ($level) {
                $player->teleport($level->getSpawnLocation());
            });
        }
        parent::__construct("マップのワールドを選択", "", $buttons);
    }

    function onClickCloseButton(Player $player): void {
    }
}