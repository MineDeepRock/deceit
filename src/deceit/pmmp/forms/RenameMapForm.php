<?php


namespace deceit\pmmp\forms;


use deceit\dao\MapDAO;
use deceit\models\Map;
use form_builder\models\custom_form_elements\Input;
use form_builder\models\CustomForm;
use pocketmine\Player;

class RenameMapForm extends CustomForm
{
    private Map $map;
    private Input $nameInputElement;

    public function __construct(Map $map) {
        $this->map = $map;
        $this->nameInputElement = new Input("", "", $map->getName());
        parent::__construct($map->getName() . "の名前を変更する", [
            $this->nameInputElement,
        ]);
    }

    function onSubmit(Player $player): void {
        $newMap = new Map(
            $this->map->getLevelName(),
            $this->nameInputElement->getResult(),
            $this->map->getStartVector(),
            $this->map->getExitVector(),
            $this->map->getOriginalExitBlockId(),
            $this->map->getFuelTankMapDataList(),
            $this->map->getFuelSpawnVectors(),
        );

        MapDAO::update($this->map->getName(), $newMap);

        $updatedMap = MapDAO::findByName($newMap->getName());
        $player->sendForm(new MapSettingForm($updatedMap));
    }

    function onClickCloseButton(Player $player): void {
        $player->sendForm(new MapSettingForm($this->map));
    }
}