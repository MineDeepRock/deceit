<?php


namespace deceit\pmmp\services;


use deceit\pmmp\entities\CadaverEntity;
use deceit\services\UpdatePlayerStateService;
use deceit\types\PlayerState;
use pocketmine\Player;

class RescueCadaverEntityPMMPService
{
    static function execute(CadaverEntity $cadaverEntity): void {
        $player = $cadaverEntity->getOwner();
        if (!$player->isOnline()) return;

        $player->setGamemode(Player::ADVENTURE);
        UpdatePlayerStateService::execute($player->getName(), PlayerState::Alive());

        if ($cadaverEntity->isAlive()) $cadaverEntity->kill();
    }
}