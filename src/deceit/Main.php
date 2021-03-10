<?php

namespace deceit;

use deceit\dao\PlayerDataDAO;
use deceit\models\PlayerData;
use deceit\pmmp\entities\BloodPackEntity;
use deceit\pmmp\entities\CadaverEntity;
use deceit\pmmp\entities\DyingPlayerEntity;
use deceit\pmmp\entities\FuelEntity;
use deceit\pmmp\entities\FuelTankEntity;
use deceit\pmmp\entities\GameCreationComputer;
use deceit\pmmp\entities\GameListBulletinBoard;
use deceit\pmmp\entities\ItemGunEntity;
use deceit\pmmp\entities\MedicineKitOnMapEntity;
use deceit\pmmp\forms\CreateGameForm;
use deceit\pmmp\forms\GameListForm;
use deceit\pmmp\forms\GameSettingForm;
use deceit\pmmp\forms\MainMapForm;
use deceit\pmmp\listeners\GameListener;
use deceit\pmmp\scoreboards\LobbyScoreboard;
use deceit\services\QuitGameService;
use deceit\storages\GameStorage;
use deceit\pmmp\utilities\GetWorldNameList;
use deceit\pmmp\utilities\SavePlayerSkin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Main extends PluginBase implements Listener
{
    public function onEnable() {
        Entity::registerEntity(BloodPackEntity::class, true, ['BloodPack']);
        Entity::registerEntity(CadaverEntity::class, true, ['Cadaver']);
        Entity::registerEntity(DyingPlayerEntity::class, true, ['Dying']);
        Entity::registerEntity(FuelEntity::class, true, ['Fuel']);
        Entity::registerEntity(FuelTankEntity::class, true, ['FuelTank']);
        Entity::registerEntity(ItemGunEntity::class, true, ['Gun']);
        Entity::registerEntity(MedicineKitOnMapEntity::class, true, ['MedicineKit']);

        DataFolderPath::init($this->getDataFolder(), $this->getFile() . "resources/");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new GameListener($this->getScheduler()), $this);

        foreach (GetWorldNameList::execute() as $worldName) {
            Server::getInstance()->loadLevel($worldName);
        }
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $playerName = $player->getName();
        SavePlayerSkin::execute($player);

        if (PlayerDataDAO::findByName($playerName) === null) {
            $playerData = new PlayerData($playerName);
            PlayerDataDAO::save($playerData);
        }

        LobbyScoreboard::send($player);

        $pk = new GameRulesChangedPacket();
        $pk->gameRules["doImmediateRespawn"] = [1, true];
        $player->sendDataPacket($pk);
    }

    public function onQuit(PlayerQuitEvent $event) {
        $playerName = $event->getPlayer()->getName();

        QuitGameService::execute($playerName);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($sender instanceof Player) {
            if ($label === "create") {
                $sender->sendForm(new CreateGameForm($this->getScheduler()));
                return true;
            }
            if ($label === "setting") {
                $playerData = PlayerDataDAO::findByName($sender->getName());
                $game = GameStorage::findById($playerData->getBelongGameId());

                if ($game === null) {
                    $sender->sendMessage("ゲームに参加していないか、ゲームのオーナではありません");
                } else {
                    $sender->sendForm(new GameSettingForm($this->getScheduler()));
                }

                return true;
            }
            if ($label === "map") {
                $sender->sendForm(new MainMapForm());
                return true;
            }
            if ($label = "gamelist") {
                $sender->sendForm(new GameListForm($sender));
                return true;
            }
        }

        return false;
    }

    public function onTapGameListBulletinBoard(EntityDamageByEntityEvent $event) {
        $bloodPackEntity = $event->getEntity();
        $attacker = $event->getDamager();
        if (!($attacker instanceof Player)) return;
        if (!($bloodPackEntity instanceof GameListBulletinBoard)) return;
        $event->setCancelled();

        $attacker->sendForm(new GameListForm($attacker));
    }

    public function onTapGameCreationComputer(EntityDamageByEntityEvent $event) {
        $bloodPackEntity = $event->getEntity();
        $attacker = $event->getDamager();
        if (!($attacker instanceof Player)) return;
        if (!($bloodPackEntity instanceof GameCreationComputer)) return;
        $event->setCancelled();

        $attacker->sendForm(new CreateGameForm($attacker));
    }

    public function onExhaust(PlayerExhaustEvent $event) {
        $event->setCancelled();
    }
}