<?php


namespace deceit\pmmp\events;


use deceit\models\GameId;
use pocketmine\event\Event;

class UpdatedGameTimerEvent extends Event
{

    private GameId $gameId;

    public function __construct(GameId $gameId) {
        $this->gameId = $gameId;
    }

    /**
     * @return GameId
     */
    public function getGameId(): GameId {
        return $this->gameId;
    }
}