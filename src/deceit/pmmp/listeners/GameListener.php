<?php

namespace deceit\pmmp\listeners;


use deceit\dao\PlayerDataDAO;
use deceit\models\Game;
use deceit\data\PlayerData;
use deceit\pmmp\blocks\ExitBlock;
use deceit\pmmp\entities\BloodPackEntity;
use deceit\pmmp\entities\CadaverEntity;
use deceit\pmmp\entities\DyingPlayerEntity;
use deceit\pmmp\entities\FuelEntity;
use deceit\pmmp\entities\FuelTankEntity;
use deceit\pmmp\entities\ItemGunEntity;
use deceit\pmmp\entities\ItemOnMapEntity;
use deceit\pmmp\events\FuelTankBecameFullEvent;
use deceit\pmmp\events\UpdatedGameDataEvent;
use deceit\pmmp\forms\ConfirmVoteForm;
use deceit\pmmp\items\FuelItem;
use deceit\pmmp\scoreboards\GameSettingsScoreboard;
use deceit\pmmp\scoreboards\OnGameScoreboard;
use deceit\pmmp\services\FinishGamePMMPService;
use deceit\pmmp\services\OpenExitPMMPService;
use deceit\pmmp\services\SendWolfChat;
use deceit\services\FinishGameService;
use deceit\services\UpdatePlayerStateService;
use deceit\storages\GameStorage;
use deceit\storages\PlayerStatusStorage;
use deceit\types\PlayerState;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\Player;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\TextFormat;


//TODO:停電機能
class GameListener implements Listener
{
    private TaskScheduler $scheduler;

    public function __construct(TaskScheduler $scheduler) {
        $this->scheduler = $scheduler;
    }

    public function onDamagedFuelTankEntity(EntityDamageEvent $event) {
        $fuelTankEntity = $event->getEntity();
        if (!($fuelTankEntity instanceof FuelTankEntity)) return;
        $event->setCancelled();

        if ($event instanceof EntityDamageByEntityEvent) {

            //Player以外ならキャンセル
            $attacker = $event->getDamager();
            if (!($attacker instanceof Player)) return;

            $attackerData = PlayerDataDAO::findByName($attacker->getName());
            $belongGameId = $attackerData->getBelongGameId();
            $fuelTankBelongGameId = $fuelTankEntity->getBelongGameId();

            //試合に参加していない or 別の試合 ならキャンセル
            if ($belongGameId === null) return;
            if (!($belongGameId->equals($fuelTankBelongGameId))) return;
            $game = GameStorage::findById($belongGameId);

            //燃料を持ってタップしたら、手に持っている分だけタンクに追加
            //人狼の場合はニセの燃料を追加
            $itemInHand = $attacker->getInventory()->getItemInHand();
            if ($itemInHand->getId() === FuelItem::ITEM_ID) {
                $fuelTank = $game->getFuelTankById($fuelTankEntity->getTankId());
                if ($fuelTank === null) return;


                $isFakeFuel = in_array($attacker->getName(), $game->getWolfNameList());
                $result = $fuelTank->addFuel($itemInHand->getCount(), $isFakeFuel);
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

    public function onDamagedFuelEntity(EntityDamageEvent $event) {
        $fuelEntity = $event->getEntity();
        if (!($fuelEntity instanceof FuelEntity)) return;

        if ($event instanceof EntityDamageByEntityEvent) {

            //Player以外ならキャンセル
            $attacker = $event->getDamager();
            if (!($attacker instanceof Player)) return;

            $attackerData = PlayerDataDAO::findByName($attacker->getName());
            $belongGameId = $attackerData->getBelongGameId();

            //試合に参加していない ならキャンセル
            if ($belongGameId === null) return;

            $attacker->getInventory()->addItem(new FuelItem());
        }
    }


    public function onFuelTankBecameFull(FuelTankBecameFullEvent $event): void {
        $gameId = $event->getBelongGameId();

        $game = GameStorage::findById($gameId);
        if ($game === null) return;

        $isAllTankFull = true;
        foreach ($game->getFuelTanks() as $fuelTank) {
            if (!$fuelTank->isFull()) $isAllTankFull = false;
        }

        //すべてのタンクが満タンになったら、脱出の出口を開く
        if ($isAllTankFull) {
            OpenExitPMMPService::execute($game);
        }
    }

    public function onGamePlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $playerData = PlayerDataDAO::findByName($player->getName());
        if (!$this->belongGameIsInProgress($playerData)) return;
        $game = GameStorage::findById($playerData->getBelongGameId());

        //人狼が死んだ場合
        $playerStatus = PlayerStatusStorage::findByName($player->getName());
        if ($playerStatus->isWolf()) {
            $playerStatus->resetBlood();
            foreach ($game->getWolfNameList() as $wolfName) {
                $wolfPlayer = Server::getInstance()->getPlayer($wolfName);
                OnGameScoreboard::update($wolfPlayer, $game);
            }
        }

        //変身中の人狼に殺された場合
        $cause = $player->getLastDamageCause();
        if (!($cause instanceof EntityDamageByEntityEvent)) return;
        $killer = $cause->getDamager();
        if (!($killer instanceof Player)) return;

        $killerStatus = PlayerStatusStorage::findByName($killer->getName());
        if ($killerStatus->nowTransforming()) {
            $dyingPlayerEntity = new DyingPlayerEntity($player->getLevel(), $playerData->getBelongGameId(), $player, true, $this->scheduler);

        } else {
            $dyingPlayerEntity = new DyingPlayerEntity($player->getLevel(), $playerData->getBelongGameId(), $player, false, $this->scheduler);

        }

        $player->setSpawn($player->getPosition());
        $dyingPlayerEntity->spawnToAll();
    }

    public function onGamePlayerRespawn(PlayerRespawnEvent $event): void {
        $player = $event->getPlayer();
        $playerData = PlayerDataDAO::findByName($player->getName());
        if (!$this->belongGameIsInProgress($playerData)) return;

        $player->setGamemode(Player::SPECTATOR);
        $player->setImmobile(true);
        UpdatePlayerStateService::execute($player->getName(), PlayerState::Dying());
    }

    public function onTapDyingPlayerEntity(EntityDamageByEntityEvent $event) {
        $dyingPlayerEntity = $event->getEntity();
        $attacker = $event->getDamager();
        if (!($attacker instanceof Player)) return;
        if (!($dyingPlayerEntity instanceof DyingPlayerEntity)) return;
        $event->setCancelled();

        //瀕死状態のエンティティとタップした人の確認

        //持ち主がオフライン
        $dyingPlayerEntityOwner = $dyingPlayerEntity->getOwner();
        if (!$dyingPlayerEntityOwner->isOnline()) return;

        //進行中のゲームに参加しているか
        $dyingPlayerEntityOwnerData = PlayerDataDAO::findByName($dyingPlayerEntityOwner->getName());
        $dyingPlayerEntityOwnerGameId = $dyingPlayerEntityOwnerData->getBelongGameId();
        $attackerData = PlayerDataDAO::findByName($attacker->getName());
        $attackerGameId = $attackerData->getBelongGameId();

        if (!$this->belongGameIsInProgress($attackerData)) return;
        if (!$this->belongGameIsInProgress($dyingPlayerEntityOwnerData)) return;

        //同じゲームに属しているか
        if (!$attackerGameId->equals($dyingPlayerEntityOwnerGameId)) return;

        //生存者しか投票できない
        $attackerStatus = PlayerStatusStorage::findByName($attacker->getName());
        if ($attackerStatus === null) return;
        if (!$attackerStatus->getState()->equals(PlayerState::Alive())) return;

        $attacker->sendForm(new ConfirmVoteForm($dyingPlayerEntity));
    }

    public function onDyingPlayerEntityDeath(EntityDeathEvent $event) {
        $event->setDrops([]);
        $entity = $event->getEntity();

        if (!($entity instanceof DyingPlayerEntity)) return;
        if ($entity->isRescued()) return;

        //本当の死
        $owner = $entity->getOwner();
        if (!$owner->isOnline()) return;

        $ownerData = PlayerDataDAO::findByName($owner->getName());
        $belongGameId = $ownerData->getBelongGameId();
        if ($belongGameId === null) return;
        $owner->setGamemode(Player::SPECTATOR);
        $owner->setImmobile(false);

        $game = GameStorage::findById($belongGameId);
        UpdatePlayerStateService::execute($owner->getName(), PlayerState::Dead());

        $cadaverEntity = new CadaverEntity($owner->getLevel(), $owner);
        $cadaverEntity->spawnToAll();

        //人狼がすべてのプレイヤーを殺したとき
        $aliveWolfCount = 0;
        $alivePlayerStatusList = PlayerStatusStorage::getAlivePlayers($belongGameId);
        foreach ($alivePlayerStatusList as $alivePlayerStatus) {
            if (in_array($alivePlayerStatus->getName(), $game->getWolfNameList())) {
                $aliveWolfCount++;
            }
        }


        if (count($alivePlayerStatusList) - $aliveWolfCount <= 0) {
            FinishGameService::execute($belongGameId);
            FinishGamePMMPService::execute($belongGameId);
        }
    }

    public function onToggleSneak(PlayerToggleSneakEvent $event) {
        $player = $event->getPlayer();
        if (!$player->isSneaking()) return;

        $playerData = PlayerDataDAO::findByName($player->getName());
        if (!$this->belongGameIsInProgress($playerData)) return;

        $game = GameStorage::findById($playerData->getBelongGameId());

        //脱出
        if ($player->getPosition()->distance($game->getMap()->getExitVector()) <= Game::ExitRange) {
            UpdatePlayerStateService::execute($player->getName(), PlayerState::Escaped());

            $player->setGamemode(Player::SPECTATOR);

            $player->sendMessage("脱出成功！！");

            foreach ($game->getPlayerNameList() as $participantName) {
                $participant = Server::getInstance()->getPlayer($participantName);
                $participant->sendMessage($player->getName() . "が脱出しました");
            }
        }
    }

    public function onTapCadaverEntity(EntityDamageByEntityEvent $event) {
        $cadaverEntity = $event->getEntity();
        $attacker = $event->getDamager();
        if (!($attacker instanceof Player)) return;
        if (!($cadaverEntity instanceof CadaverEntity)) return;
        $event->setCancelled();
    }

    public function onTapBloodPackEntity(EntityDamageByEntityEvent $event) {
        $bloodPackEntity = $event->getEntity();
        $attacker = $event->getDamager();
        if (!($attacker instanceof Player)) return;
        if (!($bloodPackEntity instanceof BloodPackEntity)) return;
        $event->setCancelled();
        $bloodPackEntity->onAttackedByPlayer($attacker);
    }

    public function onTapItemOnMapEntity(EntityDamageByEntityEvent $event): void {
        $entity = $event->getEntity();
        $attacker = $event->getDamager();
        if (!($attacker instanceof Player)) return;
        if (($entity instanceof ItemOnMapEntity) or ($entity instanceof ItemGunEntity)) {
            $event->setCancelled();
            $entity->onAttackedByPlayer($attacker);
        }
    }

    private function belongGameIsInProgress(PlayerData $playerData): bool {
        $gameId = $playerData->getBelongGameId();

        $game = GameStorage::findById($gameId);
        if ($game === null) return false;
        if (!$game->isStarted()) return false;
        if ($game->isFinished()) return false;

        return true;
    }

    //TODO : イベントいらないかも
    public function onUpdatedGameData(UpdatedGameDataEvent $event) {
        $game = GameStorage::findById($event->getGameId());
        if ($game === null) return;

        foreach ($game->getPlayerNameList() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            GameSettingsScoreboard::update($player);
        }
    }

    public function onChat(PlayerChatEvent $event) {
        $sender = $event->getPlayer();
        if (substr($event->getMessage(), 0, 1) === "!") {
            SendWolfChat::execute($sender, $event->getMessage());
        }

        $senderStatus = PlayerStatusStorage::findByName($sender->getName());
        if ($senderStatus === null) return;

        $game = GameStorage::findById($senderStatus->getBelongGameId());
        if ($game === null) {
            foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                $onlinePlayerStatus = PlayerStatusStorage::findByName($onlinePlayer->getName());
                if ($onlinePlayerStatus === null) {
                    $onlinePlayer->sendMessage("[{$sender->getName()}]" . $event->getMessage());
                }
            }

            return;
        }


        foreach ($game->getPlayerNameList() as $name) {
            $gamePlayer = Server::getInstance()->getPlayer($name);
            if ($gamePlayer === null) continue;
            if (!$gamePlayer->isOnline()) continue;

            $gamePlayer->sendMessage("[{$sender->getName()}]" . $event->getMessage());
        }
    }
}