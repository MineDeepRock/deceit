<?php


namespace deceit\pmmp\forms;


use deceit\dao\MapDAO;
use deceit\data\GunDataOnMap;
use deceit\models\Map;
use deceit\pmmp\slot_menus\GunDataOnMapSettingSlotMenu;
use form_builder\models\custom_form_elements\Dropdown;
use form_builder\models\CustomForm;
use gun_system\GunSystem;
use pocketmine\Player;
use slot_menu_system\SlotMenuSystem;

class EditGunDataOnMapForm extends CustomForm
{
    private Map $map;
    private GunDataOnMap $gunDataOnMap;
    private Dropdown $gunNameElement;

    public function __construct(Map $map, GunDataOnMap $gunDataOnMap) {
        $this->map = $map;
        $this->gunDataOnMap = $gunDataOnMap;

        $this->gunNameElement = new Dropdown("銃一覧", GunSystem::loadAllGuns(), 0);
        parent::__construct("銃を選択", [
            $this->gunNameElement,
        ]);
    }


    function onSubmit(Player $player): void {
        $gunName = $this->gunNameElement->getResult();
        $newGunDataOnMapList = [];
        foreach ($this->map->getGunDataOnMapList() as $gunDataOnMap) {
            if ($gunDataOnMap->getVector()->equals($this->gunDataOnMap->getVector())) {
                $gunDataOnMapList[] = new GunDataOnMap($gunName, $gunDataOnMap->getVector());
            } else {
                $gunDataOnMapList[] = $gunDataOnMap;
            }
        }


        MapDAO::updatePartOfMap($this->map->getName(), ["gun_data_list" => $newGunDataOnMapList]);
        $player->sendForm(new GunDataOnMapListForm(MapDAO::findByName($this->map->getName())));
    }

    function onClickCloseButton(Player $player): void {
        SlotMenuSystem::send($player, new GunDataOnMapSettingSlotMenu($this->map, $this->gunDataOnMap));
    }
}