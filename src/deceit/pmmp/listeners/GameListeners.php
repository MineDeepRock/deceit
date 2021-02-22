<?php

namespace deceit\pmmp\listeners;


use deceit\dao\PlayerStatusDAO;
use deceit\pmmp\entities\CadaverEntity;
use deceit\pmmp\entities\FuelTankEntity;
use deceit\pmmp\events\FuelTankBecameFullEvent;
use deceit\pmmp\items\FuelItem;
use deceit\storages\GameStorage;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;


class GameListeners implements Listener
{
    public function onDamagedFuelTankEntity(EntityDamageEvent $event) {
        $fuelTankEntity = $event->getEntity();
        if (!($fuelTankEntity instanceof FuelTankEntity)) return;
        $event->setCancelled();

        if ($event instanceof EntityDamageByEntityEvent) {

            //Player以外ならキャンセル
            $attacker = $event->getDamager();
            if (!($attacker instanceof Player)) return;

            $attackerStatus = PlayerStatusDAO::findByName($attacker->getName());
            $belongGameId = $attackerStatus->getBelongGameId();
            $fuelTankBelongGameId = $fuelTankEntity->getBelongGameId();

            //試合に参加していない or 別の試合 ならキャンセル
            if ($belongGameId === null or !($belongGameId->equals($fuelTankBelongGameId))) return;

            $game = GameStorage::findById($belongGameId);

            //TODO :メタで人狼を特定されないように、燃料と同じテクスチャアイテムを持ってタップしたときに～～の処理をかく
            if (in_array($attacker->getName(), $game->getWolfNameList())) {
                return;
            }

            //燃料を持ってタップしたら、手に持っている分だけタンクに追加
            $itemInHand = $attacker->getInventory()->getItemInHand();
            if ($itemInHand->getId() === FuelItem::ITEM_ID) {
                $fuelTank = $game->getFuelTankById($fuelTankEntity->getTankId());
                if ($fuelTank === null) return;

                $result = $fuelTank->addFuel($itemInHand->getCount());
                if ($result) {
                    //TODO: マックスを超えた分は消費しないように
                    $attacker->getInventory()->clear($attacker->getInventory()->getHeldItemIndex());
                    $fuelTankEntity->updateTankGauge($fuelTank->getAmountPercentage());

                    $attacker->sendPopup(TextFormat::GREEN . "タンクに燃料を入れました");
                    $attacker->sendMessage(TextFormat::GREEN . "タンクに燃料を入れました");
                }
            }

        }
    }

    public function onFuelTankBecameFull(FuelTankBecameFullEvent $event): void {
        $fulledTankId = $event->getTankId();
        $gameId = $event->getBelongGameId();

        $game = GameStorage::findById($gameId);
        if ($game === null) return;

        $isAllTankFull = true;
        foreach ($game->getFuelTanks() as $fuelTank) {
            if (!$fuelTank->isFull()) $isAllTankFull = false;
        }

        if ($isAllTankFull) {
            //TODO:脱出の出口を開く
        }
    }

    public function onGamePlayerDeath(PlayerDeathEvent $event) :void {
        $player = $event->getPlayer();
        $playerStatus = PlayerStatusDAO::findByName($player->getName());
        $gameId = $playerStatus->getBelongGameId();
        if ($gameId === null) return;

        $game = GameStorage::findById($gameId);
        if (!$game->isStarted()) return;
        if ($game->isFinished()) return;

        $player->setSpawn($player->getPosition());
        $cadaver = new CadaverEntity($player->getLevel(), $player);
        $cadaver->spawnToAll();
    }

    public function onGamePlayerRespawn(PlayerRespawnEvent $event): void {
        $player = $event->getPlayer();
        $playerStatus = PlayerStatusDAO::findByName($player->getName());
        $gameId = $playerStatus->getBelongGameId();
        if ($gameId === null) return;

        $game = GameStorage::findById($gameId);
        if (!$game->isStarted()) return;
        if ($game->isFinished()) return;

        $player->setGamemode(Player::SPECTATOR);
        $player->setImmobile(true);
    }
}