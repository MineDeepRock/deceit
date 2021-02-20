<?php

namespace deceit;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
    public function onLoad() {
        DataFolderPath::init($this->getDataFolder());
    }
}