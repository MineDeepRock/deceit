<?php

namespace deceit;

use deceit\dao\PlayerStatusDAO;
use deceit\models\PlayerStatus;
use deceit\pmmp\listeners\GameListener;
use deceit\pmmp\scoreboards\LobbyScoreboard;
use deceit\services\QuitGameService;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
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
}