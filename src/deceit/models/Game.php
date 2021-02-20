<?php

namespace deceit\models;


class Game
{
    private GameId $gameId;
    private int $maxPlayers;
    private int $wolfsCount;

    private array $playersName;
    private array $wolfsName;

    private array $fuelTanks;

    private Map $map;
    private Timer $timer;

    private function __construct(GameId $gameId, int $maxPlayers, int $wolfsCount) {
        $this->gameId = $gameId;
        $this->maxPlayers = $maxPlayers;
        $this->wolfsCount = $wolfsCount;
    }

    static function asNew(int $maxPlayers, int $wolfsCount): self {
        return new Game(GameId::asNew(), $maxPlayers, $wolfsCount);
    }


}