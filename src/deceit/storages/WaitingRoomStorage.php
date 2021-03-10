<?php


namespace deceit\storages;


use deceit\data\WaitingRoom;
use deceit\DataFolderPath;
use deceit\dto\WaitingRoomDTO;
use pocketmine\math\Vector3;

class WaitingRoomStorage
{
    /**
     * @var WaitingRoom[]
     */
    static array $waitingRoomList = [];

    //TODO:注意
    static function loadAllWaitingRooms(): void {
        $data = json_decode(file_get_contents(DataFolderPath::$waitingRoomListJson));
        foreach ($data as $vectorAsJson) {
            self::$waitingRoomList[] = WaitingRoomDTO::decode($vectorAsJson);
        }
    }

    static function add(WaitingRoom $waitingRoom): bool {
        if (self::findByVector($waitingRoom->getVector()) !== null) return false;

        self::$waitingRoomList[] = $waitingRoom;
        return true;
    }

    static function findByVector(?Vector3 $vector3): ?WaitingRoom {
        if ($vector3 === null) return null;

        foreach (self::$waitingRoomList as $waitingRoom) {
            if ($waitingRoom->getVector()->equals($vector3)) {
                return $waitingRoom;
            }
        }

        return null;
    }

    static function useRandomAvailableRoom(): ?WaitingRoom {
        foreach (self::$waitingRoomList as $key => $waitingRoom) {
            if ($waitingRoom->isAvailable()) {
                self::$waitingRoomList[$key] = new WaitingRoom($waitingRoom->getVector(), false);
                return $waitingRoom;
            }
        }

        return null;
    }

    static function returnWaitingRoom(WaitingRoom $target): void {
        foreach (self::$waitingRoomList as $key => $waitingRoom) {
            if ($waitingRoom->getVector()->equals($target->getVector())) {
                self::$waitingRoomList[$key] = new WaitingRoom($waitingRoom->getVector(), true);
            }
        }
    }
}