<?php


namespace deceit\types;


class PlayerStateOnGame
{
    private string $text;

    private function __construct(string $text) {
        $this->text = $text;
    }

    static function Alive(): PlayerStateOnGame {
        return new self("Alive");
    }

    static function Cadaver(): PlayerStateOnGame {
        return new self("Cadaver");
    }

    static function Dead(): PlayerStateOnGame {
        return new self("Dead");
    }

    static function Escaped(): PlayerStateOnGame {
        return new self("Escaped");
    }

    public function equals(?self $playerStateOnGame): bool {
        if ($playerStateOnGame === null)
            return false;

        return $this->text === $playerStateOnGame->text;
    }
}