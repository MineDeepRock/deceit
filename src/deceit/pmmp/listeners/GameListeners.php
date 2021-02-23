<?php

namespace deceit\pmmp\listeners;


use bossbar_system\BossBar;
use deceit\dao\PlayerStatusDAO;
use deceit\models\PlayerStatus;
use deceit\pmmp\BossBarTypeList;
use deceit\pmmp\entities\CadaverEntity;
use deceit\pmmp\entities\FuelTankEntity;
use deceit\pmmp\events\FuelTankBecameFullEvent;
use deceit\pmmp\events\UpdatedExitTimerEvent;
use deceit\pmmp\events\UpdatedGameTimerEvent;
use deceit\pmmp\events\VotedPlayerEvent;
use deceit\pmmp\forms\ConfirmVoteForm;
use deceit\pmmp\items\FuelItem;
use deceit\storages\GameStorage;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;


//TODO:蘇生機能
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
            $exitVector = $game->getMap()->getExitVector();
        }
    }

    public function onGamePlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $playerStatus = PlayerStatusDAO::findByName($player->getName());
        if (!$this->belongGameIsInProgress($playerStatus)) return;

        $player->setSpawn($player->getPosition());
        $cadaver = new CadaverEntity($player->getLevel(), $player);
        $cadaver->spawnToAll();
    }

    public function onGamePlayerRespawn(PlayerRespawnEvent $event): void {
        $player = $event->getPlayer();
        $playerStatus = PlayerStatusDAO::findByName($player->getName());
        if (!$this->belongGameIsInProgress($playerStatus)) return;

        $player->setGamemode(Player::SPECTATOR);
        $player->setImmobile(true);
    }

    public function onTapCadaverEntity(EntityDamageByEntityEvent $event) {
        $cadaverEntity = $event->getEntity();
        $attacker = $event->getDamager();
        if (!($attacker instanceof Player)) return;
        if (!($cadaverEntity instanceof CadaverEntity)) return;
        $event->setCancelled();

        //死体のオーナーとタップした人の確認

        //持ち主がオフライン
        $cadaverEntityOwner = $cadaverEntity->getOwner();
        if (!$cadaverEntityOwner->isOnline()) return;

        //進行中のゲームに参加しているか
        $cadaverEntityOwnerStatus = PlayerStatusDAO::findByName($cadaverEntityOwner->getName());
        $cadaverEntityOwnerGameId = $cadaverEntityOwnerStatus->getBelongGameId();
        $attackerStatus = PlayerStatusDAO::findByName($attacker->getName());
        $attackerGameId = $attackerStatus->getBelongGameId();

        if (!$this->belongGameIsInProgress($attackerStatus)) return;
        if (!$this->belongGameIsInProgress($cadaverEntityOwnerStatus)) return;

        //同じゲームに属しているか
        if (!$attackerGameId->equals($cadaverEntityOwnerGameId)) return;

        $attacker->sendForm(new ConfirmVoteForm($cadaverEntity));
    }

    public function onVotedPlayerEvent(VotedPlayerEvent $event): void {
        $cadaverEntity = $event->getCadaverEntity();
        $votedPlayerNameList = $cadaverEntity->getVotedPlayerNameList();

        $player = $cadaverEntity->getOwner();
        $playerStatus = PlayerStatusDAO::findByName($player->getName());
        if (!$this->belongGameIsInProgress($playerStatus)) return;

        $game = GameStorage::findById($playerStatus->getBelongGameId());

        $isMajority = (count($game->getAlivePlayerNameList()) - count($votedPlayerNameList) * 2) <= 0;

        if ($isMajority) {
            $cadaverEntity->kill();
        }
    }


    public function onCadaverDeath(EntityDeathEvent $event) {
        $event->setDrops([]);
        $entity = $event->getEntity();

        if (!($entity instanceof CadaverEntity)) return;

        //TODO:本当の死、スペクテイターにする
        $owner = $entity->getOwner();
        if (!$owner->isOnline()) return;

        $ownerStatus = PlayerStatusDAO::findByName($owner->getName());
        $belongGameId = $ownerStatus->getBelongGameId();
        if ($belongGameId === null) return;
        $owner->setGamemode(Player::SPECTATOR);
        $owner->setImmobile(false);

        $game = GameStorage::findById($belongGameId);
        foreach ($game->getPlayersName() as $name) {
            $player = Server::getInstance()->getPlayer($name);
            if ($player === null) return;
            if (!$player->isOnline()) return;

            $player->sendMessage(TextFormat::RED . $owner->getName() . "が処刑されました");
            $player->sendTitle(TextFormat::RED . $owner->getName() . "が処刑されました");
        }
    }

    public function onUpdatedGameTimer(UpdatedGameTimerEvent $event) {
        $gameId = $event->getGameId();
        $game = GameStorage::findById($gameId);
        if ($game === null) return;
        foreach ($game->getPlayersName() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;

            //BossBarnの更新
            $bossBar = BossBar::findByType($player, BossBarTypeList::GameTimer());
            if ($bossBar === null) return;//TODO:error
            $bossBar->updatePercentage($game->getGameTimerPercentage());
        }
    }

    public function onUpdatedExitTimer(UpdatedExitTimerEvent $event) {
        $gameId = $event->getGameId();
        $game = GameStorage::findById($gameId);
        if ($game === null) return;
        foreach ($game->getPlayersName() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;

            //BossBarnの更新
            $bossBar = BossBar::findByType($player, BossBarTypeList::ExitTimer());
            if ($bossBar === null) return;//TODO:error
            $bossBar->updatePercentage($game->getExitTimerPercentage());
        }
    }

    private function belongGameIsInProgress(PlayerStatus $playerStatus): bool {
        $gameId = $playerStatus->getBelongGameId();
        if ($gameId === null) return false;

        $game = GameStorage::findById($gameId);
        if (!$game->isStarted()) return false;
        if ($game->isFinished()) return false;

        return true;
    }
}