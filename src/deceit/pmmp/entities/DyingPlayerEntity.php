<?php

namespace deceit\pmmp\entities;


use deceit\DataFolderPath;
use deceit\pmmp\services\RescueDyingPlayerPMMPService;
use deceit\storages\GameStorage;
use deceit\storages\PlayerStatusStorage;
use deceit\types\GameId;
use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\utils\UUID;

class DyingPlayerEntity extends EntityBase
{
    const NAME = "Dying";
    public $width = 0.6;
    public $height = 0.2;

    public string $skinName = self::NAME;
    protected string $geometryId = "geometry." . self::NAME;
    protected string $geometryName = self::NAME . ".geo.json";

    private TaskScheduler $scheduler;
    private TaskHandler $limitTaskHandler;
    private TaskHandler $rescueTaskHandler;


    private Player $owner;
    private GameId $gameId;
    private bool $isRescued;

    private array $votedPlayerNameList;

    private const RescueRange = 2;
    private const MaxRescueGauge = 5;

    private ?Player $rescuingPlayer;
    private int $rescueGauge;

    public function __construct(Level $level, GameId $gameId, Player $owner, TaskScheduler $scheduler) {
        $this->owner = $owner;
        $this->gameId = $gameId;
        $this->votedPlayerNameList = [];
        $this->scheduler = $scheduler;
        $this->isRescued = false;
        $this->rescuingPlayer = null;
        $this->rescueGauge = 0;

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

    public function spawnToAll(): void {

        $this->limitTaskHandler = $this->scheduler->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick): void {
                if ($this->isAlive()) $this->kill();
            }
        ), 20 * 15);


        $this->rescueTaskHandler = $this->scheduler->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick): void {
                if ($this->rescuingPlayer === null) {
                    $this->rescueGauge = 0;
                    $this->findRescuingPlayer();

                } else if (!$this->rescuingPlayer->isOnline()) {
                    $this->rescueGauge = 0;
                    $this->findRescuingPlayer();

                } else {
                    if ($this->distance($this->rescuingPlayer) <= self::RescueRange) {
                        $this->rescueGauge++;
                        if ($this->rescueGauge === self::MaxRescueGauge) {
                            $this->isRescued = true;
                            RescueDyingPlayerPMMPService::execute($this);

                        }
                    } else {
                        $this->rescueGauge = 0;
                        $this->rescuingPlayer = null;

                    }
                }

                $this->sendCircleParticle();
            }
        ), 20 * 1);

        parent::spawnToAll();
    }

    private function findRescuingPlayer() {
        foreach ($this->getLevel()->getPlayers() as $player) {
            if ($player->isSneaking() and $player->distance($this) <= 2) {
                $this->rescuingPlayer = $player;
                break;
            }
        }
    }

    private function sendCircleParticle() {
        for ($degree = 0; $degree < 360; $degree += 10) {
            $center = $this->getPosition();

            $x = self::RescueRange * sin(deg2rad($degree));
            $z = self::RescueRange * cos(deg2rad($degree));

            $pos = $center->add($x, 1, $z);
            if ($this->rescuingPlayer === null) {
                $this->getLevel()->addParticle(new CriticalParticle($pos));

            } else if (!$this->rescuingPlayer->isOnline()) {
                $this->getLevel()->addParticle(new CriticalParticle($pos));

            } else {
                if ($degree <= floor($this->rescueGauge/self::MaxRescueGauge*360) ) {
                    $this->getLevel()->addParticle(new HappyVillagerParticle($pos));

                } else {
                    $this->getLevel()->addParticle(new HappyVillagerParticle($pos));

                }
            }
        }
    }

    protected function onDeath(): void {
        $this->limitTaskHandler->cancel();
        $this->rescueTaskHandler->cancel();

        parent::onDeath();
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

    /**
     * @return bool
     */
    public function isRescued(): bool {
        return $this->isRescued;
    }
}