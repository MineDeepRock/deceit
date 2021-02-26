<?php


namespace deceit\pmmp\slot_menus;


use deceit\dao\MapDAO;
use deceit\models\Map;
use deceit\pmmp\forms\FuelTankSettingForm;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\Player;
use slot_menu_system\SlotMenu;
use slot_menu_system\SlotMenuElement;
use slot_menu_system\SlotMenuSystem;

class FuelTankVectorSettingSlotMenu extends SlotMenu
{

    private Map $map;

    public function __construct(Map $map, Vector3 $vector3) {
        $this->map = $map;
        parent::__construct(
            [
                new SlotMenuElement(
                    ItemIds::TNT,
                    "削除",
                    function (Player $player) use ($vector3) {
                        $updatedMap = $this->updateMap($vector3);
                        SlotMenuSystem::close($player);

                        $player->sendForm(new FuelTankSettingForm($updatedMap));
                    }
                )
            ]
        );
    }


    private function updateMap(Vector3 $vector3): Map {
        $newFuelTankVectors = [];
        foreach ($this->map->getFuelTankVectors() as $fuelTankVector) {
            if (!$fuelTankVector->equals($vector3)) {
                $newFuelTankVectors[] = $fuelTankVector;
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
        MapDAO::update($newMap);

        return MapDao::findByName($this->map->getName());
    }
}