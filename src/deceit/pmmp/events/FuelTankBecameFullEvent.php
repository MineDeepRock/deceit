<?php


namespace deceit\pmmp\events;


use deceit\models\FuelTankId;
use deceit\models\GameId;
use pocketmine\event\Event;

class FuelTankBecameFullEvent extends Event
{
    protected $eventName = "FuelTankBecameFull";

    private FuelTankId $tankId;
    private GameId $belongGameId;

    public function __construct(GameId $belongGameId, FuelTankId $tankId) {
        $this->tankId = $tankId;
        $this->belongGameId = $belongGameId;
    }

    /**
     * @return FuelTankId
     */
    public function getTankId(): FuelTankId {
        return $this->tankId;
    }

    /**
     * @return GameId
     */
    public function getBelongGameId(): GameId {
        return $this->belongGameId;
    }
}