<?php


namespace deceit\pmmp\forms;


use deceit\dao\MapDAO;
use deceit\services\CreateNewMapService;
use deceit\pmmp\utilities\GetWorldNameList;
use form_builder\models\custom_form_elements\Dropdown;
use form_builder\models\custom_form_elements\Input;
use form_builder\models\CustomForm;
use pocketmine\Player;
use pocketmine\Server;

class CreateNewMapForm extends CustomForm
{
    private Input $inputNameElement;
    private Dropdown $selectWorldElement;

    public function __construct() {
        $worldNames = GetWorldNameList::execute();
        $this->inputNameElement = new Input("", "", "");
        $this->selectWorldElement = new Dropdown("ワールドを選択", $worldNames);
        parent::__construct(
            "マップを作成",
            [
                $this->inputNameElement,
                $this->selectWorldElement
            ]
        );
    }

    function onSubmit(Player $player): void {
        $mapName = $this->inputNameElement->getResult();
        $levelName = $this->selectWorldElement->getResult();
        $level = Server::getInstance()->getLevelByName($levelName);
        $result = CreateNewMapService::execute($levelName, $mapName, $level->getSpawnLocation());

        if ($result) {
            $level = Server::getInstance()->getLevelByName($levelName);
            $player->teleport($level->getSpawnLocation());

            $player->sendForm(new MapSettingForm(MapDAO::findByName($mapName)));
        }
    }

    function onClickCloseButton(Player $player): void {
        $player->sendForm(new MainMapForm());
    }
}