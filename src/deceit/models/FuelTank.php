<?php


namespace deceit\models;


use deceit\pmmp\events\FuelTankBecameFullEvent;
use deceit\types\FuelTankId;
use deceit\types\GameId;

class FuelTank
{
    private GameId $belongGameId;
    private FuelTankId $tankId;

    private int $capacity;
    private int $storageAmount;

    private int $fakeStorageAmount;
    private int $isOnceFulled;

    public function __construct(GameId $gameId) {
        $this->belongGameId = $gameId;
        $this->tankId = FuelTankId::asNew();
        $this->capacity = 200;
        $this->storageAmount = 0;
        $this->fakeStorageAmount = 0;
        $this->isOnceFulled = false;
    }

    public function addFuel(int $fuelCount, bool $isFake = false): bool {
        if ($this->capacity >= $this->storageAmount) return false;

        $fuelAmount = 10 * $fuelCount;
        $this->storageAmount += $fuelAmount;
        if ($isFake) $this->fakeStorageAmount += $fuelAmount;

        if ($this->storageAmount >= $this->capacity) {
            $this->storageAmount = $this->capacity;

            if ($this->isOnceFulled) {
                $this->storageAmount -= $this->fakeStorageAmount;
            } else {
                $event = new FuelTankBecameFullEvent($this->belongGameId, $this->tankId);
                $event->call();
            }
        }

        return true;
    }

    public function reduce(int $value): void {
        $this->storageAmount -= $value;

        if ($this->storageAmount < 0) $this->storageAmount = 0;
    }

    /**
     * @return FuelTankId
     */
    public function getTankId(): FuelTankId {
        return $this->tankId;
    }

    /**
     * @return int
     */
    public function getCapacity(): int {
        return $this->capacity;
    }

    /**
     * @return int
     */
    public function getStorageAmount(): int {
        return $this->storageAmount;
    }

    public function getAmountPercentage(): float {
        if ($this->storageAmount === 0) return 0;
        return $this->storageAmount / $this->capacity;
    }

    public function isFull(): bool {
        return $this->storageAmount === $this->capacity;
    }
}