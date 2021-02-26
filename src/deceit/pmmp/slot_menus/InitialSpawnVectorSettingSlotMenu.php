<?php


namespace deceit\pmmp\slot_menus;


use deceit\dao\MapDAO;
use deceit\models\Map;
use deceit\pmmp\forms\MapSettingForm;
use pocketmine\block\Block;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\Player;
use slot_menu_system\SlotMenu;
use slot_menu_system\SlotMenuElement;

class InitialSpawnVectorSettingSlotMenu extends SlotMenu
{

    private Map $map;

    public function __construct(Map $map) {
        $this->map = $map;
        parent::__construct(
            [
                new SlotMenuElement(
                    ItemIds::DIAMOND_SWORD,
                    "タップでスポーン位置を設定",
                    function (Player $player) {
                        $updatedMap = $this->updateMap($player);

                        $player->sendForm(new MapSettingForm($updatedMap));
                    },
                    function (Player $player, Block $block) {
                        $updatedMap = $this->updateMap($block);

                        $player->sendForm(new MapSettingForm($updatedMap));
                    }
                )
            ]
        );
    }


    private function updateMap(Vector3 $vector3): Map {
        $newMap = new Map(
            $this->map->getLevelName(),
            $this->map->getName(),
            $vector3,
            $this->map->getExitVector(),
            $this->map->getOriginalExitBlockId(),
            $this->map->getFuelTankVectors(),
            $this->map->getFuelSpawnVectors(),
        );
        MapDAO::update($newMap);

        return MapDao::findByName($this->map->getName());
    }
}