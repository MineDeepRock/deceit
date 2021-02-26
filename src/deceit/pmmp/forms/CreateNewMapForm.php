<?php


namespace deceit\pmmp\forms;


use deceit\dao\MapDAO;
use deceit\services\CreateNewMapService;
use deceit\utilities\GetWorldNameList;
use form_builder\models\custom_form_elements\Input;
use form_builder\models\custom_form_elements\StepSlider;
use form_builder\models\CustomForm;
use pocketmine\Player;
use pocketmine\Server;

class CreateNewMapForm extends CustomForm
{
    private Input $inputNameElement;
    private StepSlider $selectWorldElement;

    public function __construct() {
        $worldNames = GetWorldNameList::execute();
        $this->inputNameElement = new Input("", "", "");
        $this->selectWorldElement = new StepSlider("ワールドを選択", $worldNames, 0);
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
            $player->sendForm(new MapSettingForm(MapDAO::findByName($mapName)));
        }
    }

    function onClickCloseButton(Player $player): void {
        $player->sendForm(new MainMapForm());
    }
}