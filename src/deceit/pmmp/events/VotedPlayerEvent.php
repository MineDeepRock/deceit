<?php


namespace deceit\pmmp\events;


use deceit\pmmp\entities\CadaverEntity;
use pocketmine\event\Event;

class VotedPlayerEvent extends Event
{
    private CadaverEntity $cadaverEntity;

    public function __construct(CadaverEntity $cadaverEntity) {
        $this->cadaverEntity = $cadaverEntity;
    }

    /**
     * @return CadaverEntity
     */
    public function getCadaverEntity(): CadaverEntity {
        return $this->cadaverEntity;
    }
}