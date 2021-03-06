<?php


namespace deceit\pmmp\entities;


use deceit\dao\PlayerDataDAO;
use deceit\DataFolderPath;
use deceit\storages\GameStorage;
use deceit\storages\PlayerStatusStorage;
use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class BloodPackEntity extends EntityBase
{
    const NAME = "BloodPack";
    private int $leftOfBlood;

    public function __construct(Level $level, CompoundTag $nbt) {
        $this->leftOfBlood = 2;
        parent::__construct($level, $nbt);
    }

    public function onAttackedByPlayer(Player $player): void {
        if ($this->leftOfBlood === 0) return;

        $playerData = PlayerDataDAO::findByName($player->getName());
        $game = GameStorage::findById($playerData->getBelongGameId());
        if ($game === null) return;
        if (!$game->isStarted() or $game->isFinished()) return;

        if (in_array($player->getName(), $game->getWolfNameList())) {
            $status = PlayerStatusStorage::findByName($player->getName());
            $result = $status->addBlood();
            if (!$result) return;

            $this->leftOfBlood--;
            if ($this->leftOfBlood === 1) {
                $this->skinName = "HalfBloodPack";
            } else if ($this->leftOfBlood === 0) {
                $this->skinName = "EmptyBloodPack";
            }

            $this->setSkin(new Skin(
                "Standard_CustomSlim",
                file_get_contents(DataFolderPath::$skin . $this->skinName . ".skin"),
                "",
                $this->geometryId,
                file_get_contents(DataFolderPath::$geometry . $this->geometryName)
            ));
        }
    }
}