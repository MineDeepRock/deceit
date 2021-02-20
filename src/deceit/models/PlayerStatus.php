<?php


namespace deceit\models;


//一時的なプレイヤーデータは含まない。
class PlayerStatus
{
    private string $name;
    private ?GameId $belongGameId;//BelongGameIdは一時的だけど例外

    //TODO:level,money,etc,,
    public function __construct(string $name, ?GameId $belongGameId = null) {
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
     * @return GameId|null
     */
    public function getBelongGameId(): ?GameId {
        return $this->belongGameId;
    }
}