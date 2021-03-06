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
use slot_menu_system\SlotMenuSystem;

class ExitVectorSettingSlotMenu extends SlotMenu
{

    private Map $map;

    public function __construct(Map $map) {
        $this->map = $map;
        parent::__construct(
            [
                new SlotMenuElement(
                    ItemIds::DIAMOND_SWORD,
                    "タップで出口を設定",
                    function (Player $player) {
                        $updatedMap = $this->updateMap($player);

                        $player->sendForm(new MapSettingForm($updatedMap));
                    },
                    function (Player $player, Block $block) {
                        $updatedMap = $this->updateMap($block);
                        SlotMenuSystem::close($player);

                        $player->sendForm(new MapSettingForm($updatedMap));
                    }
                ),
                new SlotMenuElement(
                    ItemIds::DROPPER,
                    "戻る",
                    function (Player $player) {
                        SlotMenuSystem::close($player);
                        $player->sendForm(new MapSettingForm($this->map));
                    },
                    null,
                    8
                )
            ]
        );
    }


    private function updateMap(Vector3 $vector3): Map {
        MapDAO::updatePartOfMap($this->map->getName(), ["exit_vector" => $vector3]);

        return MapDao::findByName($this->map->getName());
    }
}