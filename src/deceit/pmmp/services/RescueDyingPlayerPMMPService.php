<?php


namespace deceit\pmmp\services;


use deceit\pmmp\entities\DyingPlayerEntity;
use deceit\services\UpdatePlayerStateService;
use deceit\types\PlayerState;
use pocketmine\Player;

class RescueDyingPlayerPMMPService
{
    static function execute(DyingPlayerEntity $dyingPlayerEntity): void {
        $player = $dyingPlayerEntity->getOwner();
        if (!$player->isOnline()) return;

        $player->setGamemode(Player::ADVENTURE);
        $player->teleport($dyingPlayerEntity);
        UpdatePlayerStateService::execute($player->getName(), PlayerState::Alive());

        if ($dyingPlayerEntity->isAlive()) $dyingPlayerEntity->kill();
    }
}