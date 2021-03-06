<?php


namespace deceit\models;


use deceit\types\GameId;
use deceit\types\PlayerState;

//ゲーム中にしか使わない値を持つ
class PlayerStatus
{
    private string $name;
    private GameId $belongGameId;

    private PlayerState $state;

    public function __construct(string $name, GameId $belongGameId, PlayerState $state) {
        $this->name = $name;
        $this->belongGameId = $belongGameId;
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return GameId
     */
    public function getBelongGameId(): GameId {
        return $this->belongGameId;
    }

    /**
     * @return PlayerState
     */
    public function getState(): PlayerState {
        return $this->state;
    }
}