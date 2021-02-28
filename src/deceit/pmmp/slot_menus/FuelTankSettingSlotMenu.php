<?php


namespace deceit\pmmp\slot_menus;


use deceit\dao\MapDAO;
use deceit\models\FuelTankMapData;
use deceit\models\Map;
use deceit\pmmp\forms\EditFuelTankCapacityForm;
use deceit\pmmp\forms\FuelTankListForm;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\Player;
use slot_menu_system\SlotMenu;
use slot_menu_system\SlotMenuElement;
use slot_menu_system\SlotMenuSystem;

class FuelTankSettingSlotMenu extends SlotMenu
{

    private Map $map;

    public function __construct(Map $map, FuelTankMapData $fuelTankMapData) {
        $this->map = $map;
        parent::__construct(
            [
                new SlotMenuElement(
                    ItemIds::TNT,
                    "削除",
                    function (Player $player) use ($fuelTankMapData) {
                        $updatedMap = $this->updateMap($fuelTankMapData->getVector());
                        SlotMenuSystem::close($player);

                        $player->sendForm(new FuelTankListForm($updatedMap));
                    }
                ),
                new SlotMenuElement(
                    ItemIds::NAME_TAG,
                    "容量を変更",
                    function (Player $player) use ($fuelTankMapData) {
                        SlotMenuSystem::close($player);

                        $player->sendForm(new EditFuelTankCapacityForm($this->map, $fuelTankMapData));
                    }
                ),

                new SlotMenuElement(
                    ItemIds::DROPPER,
                    "戻る",
                    function (Player $player) {
                        SlotMenuSystem::close($player);
                        $player->sendForm(new FuelTankListForm($this->map));
                    },
                    null,
                    8
                )
            ]
        );
    }


    private function updateMap(Vector3 $vector3): Map {
        $newFuelTankVectors = [];
        foreach ($this->map->getFuelTankMapDataList() as $fuelTankMapData) {
            if (!$fuelTankMapData->getVector()->equals($vector3)) {
                $newFuelTankVectors[] = $fuelTankMapData;
            }
        }

        $newMap = new Map(
            $this->map->getLevelName(),
            $this->map->getName(),
            $this->map->getStartVector(),
            $this->map->getExitVector(),
            $this->map->getOriginalExitBlockId(),
            $newFuelTankVectors,
            $this->map->getFuelSpawnVectors(),
        );
        MapDAO::update($this->map->getName(), $newMap);

        return MapDao::findByName($this->map->getName());
    }
}