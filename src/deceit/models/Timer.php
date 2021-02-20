<?php


namespace deceit\models;


use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskScheduler;

class Timer
{
    private int $initialTime;
    private int $timeLeft;

    private TaskScheduler $scheduler;

    public function __construct(int $initialTime, int $timeLeft, TaskScheduler $scheduler) {
        $this->timeLeft = $timeLeft;
        $this->initialTime = $initialTime;
        $this->scheduler = $scheduler;
    }

    static function asNew(int $initialTime, TaskScheduler $scheduler): self {
        return new Timer($initialTime, 0, $scheduler);
    }

    public function start(): void {
        $this->scheduler->scheduleDelayedRepeatingTask(new ClosureTask(
            function (int $currentTick): void {
                $this->timeLeft -= 1;
                //TODO:event
            }
        ), 20, 20);
    }
}