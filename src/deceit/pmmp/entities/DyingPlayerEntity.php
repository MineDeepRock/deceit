<?php

namespace deceit\pmmp\entities;


use deceit\DataFolderPath;
use deceit\storages\GameStorage;
use deceit\storages\PlayerStatusStorage;
use deceit\types\GameId;
use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\utils\UUID;

class DyingPlayerEntity extends EntityBase
{
    const NAME = "DyingPlayerEntity";
    public $width = 0.6;
    public $height = 0.2;

    public string $skinName = self::NAME;
    protected string $geometryId = "geometry." . self::NAME;
    protected string $geometryName = self::NAME . ".geo.json";

    private Player $owner;
    private GameId $gameId;

    private array $votedPlayerNameList;

    public function __construct(Level $level, GameId $gameId, Player $owner) {
        $this->owner = $owner;
        $this->gameId = $gameId;
        $this->votedPlayerNameList = [];
        $nbt = new CompoundTag('', [
            'Pos' => new ListTag('Pos', [
                new DoubleTag('', $owner->getX()),
                new DoubleTag('', $owner->getY()),
                new DoubleTag('', $owner->getZ())
            ]),
            'Motion' => new ListTag('Motion', [
                new DoubleTag('', 0),
                new DoubleTag('', 0),
                new DoubleTag('', 0)
            ]),
            'Rotation' => new ListTag('Rotation', [
                new FloatTag("", $owner->getYaw()),
                new FloatTag("", 0)
            ]),
        ]);
        $this->uuid = UUID::fromRandom();
        $this->initSkin($owner);

        parent::__construct($level, $nbt);
        $this->setRotation($this->yaw, $this->pitch);
        $this->setNameTagAlwaysVisible(false);
        $this->sendSkin();
    }

    private function initSkin(Player $player): void {
        $this->setSkin(new Skin(
            "Standard_CustomSlim",
            $player->getSkin()->getSkinData(),
            "",
            $this->geometryId,
            file_get_contents(DataFolderPath::$geometry . $this->geometryName)
        ));
    }

    /**
     * @return Player
     */
    public function getOwner(): Player {
        return $this->owner;
    }

    /**
     * @return array
     */
    public function getVotedPlayerNameList(): array {
        return $this->votedPlayerNameList;
    }

    public function vote(string $playerName): bool {
        $game = GameStorage::findById($this - $this->gameId);
        if ($game === null) return false;
        if (!$game->isStarted()) return false;
        if ($game->isFinished()) return false;

        if (!$this->isAlive()) return false;

        if (in_array($playerName, $this->votedPlayerNameList)) return false;
        $this->votedPlayerNameList[] = $playerName;

        $playersCanVoteCount =
            count(PlayerStatusStorage::getAlivePlayers($game->getGameId())) +
            count(PlayerStatusStorage::getDyingPlayers($game->getGameId()));

        $isMajority = $playersCanVoteCount - count($this->votedPlayerNameList) * 2 <= 0;

        if ($isMajority) $this->kill();
        return true;
    }
}