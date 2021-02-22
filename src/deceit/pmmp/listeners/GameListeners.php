<?php

namespace deceit\pmmp\listeners;


use deceit\dao\PlayerStatusDAO;
use deceit\pmmp\entities\FuelTankEntity;
use deceit\pmmp\items\FuelItem;
use deceit\storages\GameStorage;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
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
}