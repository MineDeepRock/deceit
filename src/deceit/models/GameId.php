<?php


namespace deceit\models;


class GameId
{
    private string $id;

    private function __construct(string $id) {
        $this->id = $id;
    }

    static function asNew(): self {
        return new GameId(uniqid());
    }
    public function __toString() {
        return $this->id;
    }

    public function equals(?GameId $GameId): bool {
        if ($GameId === null)
            return false;

        return $this->id === $GameId->id;
    }
}