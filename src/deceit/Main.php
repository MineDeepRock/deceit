<?php

namespace deceit;

use deceit\dao\PlayerStatusDAO;
use deceit\models\PlayerStatus;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{
    public function onLoad() {
        DataFolderPath::init($this->getDataFolder());
    }

    public function onJoin(PlayerJoinEvent $event) {
        $playerName = $event->getPlayer()->getName();

        if (PlayerStatusDAO::findByName($playerName) === null) {
            $status = new PlayerStatus($playerName);
            PlayerStatusDAO::save($status);
        }
    }
}