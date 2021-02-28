<?php


namespace deceit\pmmp\slot_menus;


use deceit\dao\MapDAO;
use deceit\models\Map;
use deceit\pmmp\forms\FuelSpawnVectorSettingForm;
use pocketmine\block\Block;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\Player;
use slot_menu_system\SlotMenu;
use slot_menu_system\SlotMenuElement;
use slot_menu_system\SlotMenuSystem;

class AddFuelSpawnVectorSlotMenu extends SlotMenu
{

    private Map $map;

    public function __construct(Map $map) {
        $this->map = $map;
        parent::__construct(
            [
                new SlotMenuElement(
                    ItemIds::DIAMOND_BLOCK,
                    "選択",
                    function (Player $player) {
                        $updatedMap = $this->updateMap($player);
                        SlotMenuSystem::close($player);

                        $player->sendForm(new FuelSpawnVectorSettingForm($updatedMap));
                    },
                    function (Player $player, Block $block) {
                        $updatedMap = $this->updateMap($block);
                        SlotMenuSystem::close($player);

                        $player->sendForm(new FuelSpawnVectorSettingForm($updatedMap));
                    }
                )
            ]
        );
    }


    private function updateMap(Vector3 $vector3): Map {

        $newFuelSpawnVectors = $this->map->getFuelSpawnVectors();
        $newFuelSpawnVectors[] = $vector3;

        $newMap = new Map(
            $this->map->getLevelName(),
            $this->map->getName(),
            $this->map->getStartVector(),
            $this->map->getExitVector(),
            $this->map->getOriginalExitBlockId(),
            $this->map->getFuelTankMapDataList(),
            $newFuelSpawnVectors,
        );
        MapDAO::update($this->map->getName(), $newMap);

        return MapDao::findByName($this->map->getName());
    }
}