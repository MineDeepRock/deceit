<?php


namespace deceit\pmmp\slot_menus;


use deceit\dao\MapDAO;
use deceit\models\Map;
use deceit\pmmp\forms\FuelSpawnVectorSettingForm;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\Player;
use slot_menu_system\SlotMenu;
use slot_menu_system\SlotMenuElement;
use slot_menu_system\SlotMenuSystem;

class FuelVectorSettingSlotMenu extends SlotMenu
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

                        $player->sendForm(new FuelSpawnVectorSettingForm($updatedMap));
                    }
                )
            ]
        );
    }


    private function updateMap(Vector3 $vector3): Map {
        $newFuelSpawnVectors = [];
        foreach ($this->map->getFuelSpawnVectors() as $fuelSpawnVector) {
            if (!$fuelSpawnVector->equals($vector3)) {
                $newFuelSpawnVectors[] = $fuelSpawnVector;
            }

        }
        $newMap = new Map(
            $this->map->getLevelName(),
            $this->map->getName(),
            $this->map->getStartVector(),
            $this->map->getExitVector(),
            $this->map->getOriginalExitBlockId(),
            $this->map->getFuelTankVectors(),
            $newFuelSpawnVectors,
        );
        MapDAO::update($newMap);

        return MapDao::findByName($this->map->getName());
    }
}