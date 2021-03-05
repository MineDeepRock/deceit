<?php

namespace deceit\pmmp\listeners;


use deceit\dao\PlayerStatusDAO;
use deceit\models\PlayerStatus;
use deceit\pmmp\blocks\ExitBlock;
use deceit\pmmp\entities\CadaverEntity;
use deceit\pmmp\entities\FuelEntity;
use deceit\pmmp\entities\FuelTankEntity;
use deceit\pmmp\events\FuelTankBecameFullEvent;
use deceit\pmmp\events\UpdatedGameDataEvent;
use deceit\pmmp\forms\ConfirmVoteForm;
use deceit\pmmp\items\FuelItem;
use deceit\pmmp\scoreboards\GameSettingsScoreboard;
use deceit\pmmp\services\FinishGamePMMPService;
use deceit\pmmp\services\OpenExitPMMPService;
use deceit\services\FinishGameService;
use deceit\services\UpdatePlayerStateOnGameService;
use deceit\storages\GameStorage;
use deceit\storages\PlayerDataOnGameStorage;
use deceit\types\PlayerStateOnGame;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\TextFormat;


//TODO:蘇生機能
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

            $attackerStatus = PlayerStatusDAO::findByName($attacker->getName());
            $belongGameId = $attackerStatus->getBelongGameId();
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

            $attackerStatus = PlayerStatusDAO::findByName($attacker->getName());
            $belongGameId = $attackerStatus->getBelongGameId();

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
            OpenExitPMMPService::execute($game->getMap());
        }
    }

    public function onGamePlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $playerStatus = PlayerStatusDAO::findByName($player->getName());
        if (!$this->belongGameIsInProgress($playerStatus)) return;

        $player->setSpawn($player->getPosition());
        $cadaver = new CadaverEntity($player->getLevel(), $playerStatus->getBelongGameId(), $player);
        $cadaver->spawnToAll();

        //15秒間放置されると死亡する
        $playerName = $player->getName();
        $level = $player->getLevel();
        $this->scheduler->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick) use ($playerName, $level): void {
                foreach ($level->getEntities() as $entity) {
                    if (!($entity instanceof CadaverEntity)) continue;
                    $owner = $entity->getOwner();
                    if ($owner === null) continue;
                    if ($owner->getName() === $playerName) {
                        $entity->kill();
                    }
                }
            }
        ), 20 * 15);
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
        UpdatePlayerStateOnGameService::execute($owner->getName(), PlayerStateOnGame::Dead());

        //人狼がすべてのプレイヤーを殺したとき
        $aliveWolfCount = 0;
        $alivePlayerDataList = PlayerDataOnGameStorage::getAlivePlayers($belongGameId);
        foreach ($alivePlayerDataList as $alivePlayerData) {
            if (in_array($alivePlayerData->getName(), $game->getWolfNameList())) {
                $aliveWolfCount++;
            }
        }

        //TODO : 自己蘇生を考慮する
        if (count($alivePlayerDataList) - $aliveWolfCount <= 0) {
            FinishGameService::execute($belongGameId);
            FinishGamePMMPService::execute($belongGameId);
        }
    }

    public function onToggleSneak(PlayerToggleSneakEvent $event) {
        $player = $event->getPlayer();
        if (!$player->isSneaking()) return;

        $playerStatus = PlayerStatusDAO::findByName($player->getName());
        if (!$this->belongGameIsInProgress($playerStatus)) return;

        $game = GameStorage::findById($playerStatus->getBelongGameId());
        $levelName = $game->getMap()->getLevelName();
        $level = Server::getInstance()->getLevelByName($levelName);

        $blockUnderPlayer = $level->getBlock($player);

        //脱出
        //TODO:時間がかかるようにする
        if ($blockUnderPlayer->getId() === ExitBlock::ID) {
            UpdatePlayerStateOnGameService::execute($player->getName(), PlayerStateOnGame::Escaped());

            $player->setGamemode(Player::SPECTATOR);

            $player->sendMessage("脱出成功！！");

            foreach ($game->getPlayerNameList() as $participantName) {
                $participant = Server::getInstance()->getPlayer($participantName);
                $participant->sendMessage($player->getName() . "が脱出しました");
            }
        }
    }

    private function belongGameIsInProgress(PlayerStatus $playerStatus): bool {
        $gameId = $playerStatus->getBelongGameId();

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
}