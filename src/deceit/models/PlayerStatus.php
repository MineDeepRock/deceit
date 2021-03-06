<?php


namespace deceit\models;


use deceit\types\GameId;
use deceit\types\PlayerStateOnGame;

//ゲーム中にしか使わない値を持つ
class PlayerStatus
{
    private string $name;
    private GameId $belongGameId;

    private PlayerStateOnGame $state;

    public function __construct(string $name, GameId $belongGameId, PlayerStateOnGame $state, bool $isWolf = false) {
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
     * @return PlayerStateOnGame
     */
    public function getState(): PlayerStateOnGame {
        return $this->state;
    }
}