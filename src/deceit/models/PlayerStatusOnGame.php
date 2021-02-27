<?php


namespace deceit\models;


use deceit\types\GameId;

class PlayerStatusOnGame
{
    private string $name;
    private GameId $belongGameId;


    public function __construct(string $name, GameId $belongGameId) {
        $this->name = $name;
        $this->belongGameId = $belongGameId;
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
}