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

    public function __construct(Map $map, FuelTankMapData $fuelTankMapData) {
        $this->map = $map;
        $this->fuelTankMapData = $fuelTankMapData;

        $this->capacitySlider = new Slider("容量", 1, 100, $fuelTankMapData->getCapacity());
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

        MapDAO::updatePartOfMap($this->map->getName(), ["fuel_tanks" => $fuelTankMapDataList]);

        $updatedMap = MapDAO::findByName($this->map->getName());
        $player->sendForm(new FuelTankListForm($updatedMap));
    }

    function onClickCloseButton(Player $player): void {
        $player->sendForm(new FuelTankListForm($this->map));
    }
}