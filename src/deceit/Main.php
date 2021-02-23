<?php

namespace deceit;

use deceit\dao\PlayerStatusDAO;
use deceit\models\PlayerStatus;
use deceit\pmmp\forms\CreateGameForm;
use deceit\pmmp\forms\GameSettingForm;
use deceit\pmmp\listeners\GameListener;
use deceit\pmmp\scoreboards\LobbyScoreboard;
use deceit\services\QuitGameService;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{
    public function onLoad() {
        DataFolderPath::init($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents(new GameListener($this->getScheduler()), $this);
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $playerName = $player->getName();

        if (PlayerStatusDAO::findByName($playerName) === null) {
            $status = new PlayerStatus($playerName);
            PlayerStatusDAO::save($status);
        }

        LobbyScoreboard::send($player);
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
                $sender->sendForm(new GameSettingForm());
                return true;
            }
        }

        return false;
    }
}