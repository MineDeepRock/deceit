<?php


namespace deceit\models;


use deceit\types\GameId;
use deceit\types\PlayerState;
use pocketmine\scheduler\TaskScheduler;

//ゲーム中にしか使わない値を持つ
class PlayerStatus
{
    private string $name;
    private GameId $belongGameId;

    private PlayerState $state;

    private bool $isWolf;
    private int $bloodTank;
    private TransformTimer $transformTimer;

    public function __construct(string $name, GameId $belongGameId, PlayerState $state, bool $isWolf = false, TaskScheduler $taskScheduler = null) {
        $this->name = $name;
        $this->belongGameId = $belongGameId;
        $this->state = $state;
        $this->isWolf = $isWolf;
        $this->bloodTank = 0;

        if ($this->isWolf) {
            $this->transformTimer = new TransformTimer($name, $taskScheduler);
        }
    }

    public function addBlood(): bool {
        if (!$this->isWolf) return false;
        if ($this->bloodTank >= 5) return false;
        $this->bloodTank++;

        return true;
    }

    public function canTransform(): bool {
        return $this->bloodTank === 5;
    }

    public function startTransformTimer(): bool {
        if (!$this->isWolf) return false;
        $this->bloodTank = 0;
        $this->transformTimer->start();

        return true;
    }

    public function stopTransformTimer(): bool {
        if (!$this->isWolf) return false;
        $this->transformTimer->stop();

        return true;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return GameId
     */
    public function getBelongGameId(): GameId {
        return $this->belongGameId;
    }

    /**
     * @return PlayerState
     */
    public function getState(): PlayerState {
        return $this->state;
    }
}