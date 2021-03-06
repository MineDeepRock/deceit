<?php


namespace deceit\pmmp\slot_menus;


use deceit\data\WaitingRoom;
use deceit\pmmp\forms\WaitingRoomListForm;
use deceit\storages\WaitingRoomStorage;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use slot_menu_system\SlotMenu;
use slot_menu_system\SlotMenuElement;
use slot_menu_system\SlotMenuSystem;

class WaitingRoomSettingSlotMenu extends SlotMenu
{
    public function __construct(WaitingRoom $waitingRoom) {
        parent::__construct(
            [
                new SlotMenuElement(
                    ItemIds::TNT,
                    "削除",
                    function (Player $player) use ($waitingRoom) {
                        WaitingRoomStorage::delete($waitingRoom);
                        SlotMenuSystem::close($player);

                        $player->sendForm(new WaitingRoomListForm());
                    }
                ),

                new SlotMenuElement(
                    ItemIds::DROPPER,
                    "戻る",
                    function (Player $player)  {
                        SlotMenuSystem::close($player);
                        $player->sendForm(new WaitingRoomListForm());
                    },
                    null,
                    8
                )
            ]
        );
    }
}