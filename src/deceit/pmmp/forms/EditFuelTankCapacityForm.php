<?php


namespace deceit\pmmp\forms;


use deceit\dao\MapDAO;
use deceit\models\FuelTankMapData;
use deceit\models\Map;
use form_builder\models\custom_form_elements\Slider;
use form_builder\models\CustomForm;
use pocketmine\Player;

class EditFuelTankCapacityForm extends CustomForm
{
    private Map $map;
    private FuelTankMapData $fuelTankMapData;
    private Slider $capacitySlider;

    public function __construct(Map $map,FuelTankMapData $fuelTankMapData) {
        $this->map = $map;
        $this->fuelTankMapData = $fuelTankMapData;

        $this->capacitySlider = new Slider("容量", 1, 100, 20);
        parent::__construct("容量を変更する", [
            $this->capacitySlider,
        ]);
    }

    function onSubmit(Player $player): void {
        $capacity = $this->capacitySlider->getResult();

        $fuelTankMapDataList = [];

        foreach ($this->map->getFuelTankMapDataList() as $fuelTankMapData) {
            if ($fuelTankMapData->getVector()->equals($this->fuelTankMapData->getVector())) {
                $fuelTankMapDataList[] = new FuelTankMapData(
                    $capacity,
                    $this->fuelTankMapData->getVector(),
                );
            } else {
                $fuelTankMapDataList[] = $fuelTankMapData;
            }
        }



        $newMap = new Map(
            $this->map->getLevelName(),
            $this->map->getName(),
            $this->map->getStartVector(),
            $this->map->getExitVector(),
            $this->map->getOriginalExitBlockId(),
            $fuelTankMapDataList,
            $this->map->getFuelSpawnVectors(),
        );

        MapDAO::update($this->map->getName(), $newMap);

        $updatedMap = MapDAO::findByName($newMap->getName());
        $player->sendForm(new FuelTankListForm($updatedMap));
    }

    function onClickCloseButton(Player $player): void {
        $player->sendForm(new FuelTankListForm($this->map));
    }
}