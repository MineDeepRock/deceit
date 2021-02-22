<?php


namespace deceit\models;


use deceit\pmmp\events\FuelTankBecameFullEvent;

class FuelTank
{
    private GameId $belongGameId;
    private FuelTankId $tankId;

    private int $capacity;
    private int $storageAmount;

    private function __construct(GameId $gameId, FuelTankId $tankId, int $capacity, int $storageAmount) {
        $this->belongGameId = $gameId;
        $this->tankId = $tankId;
        $this->capacity = $capacity;
        $this->storageAmount = $storageAmount;
    }

    static function asNew(GameId $gameId): FuelTank {
        return new FuelTank($gameId, FuelTankId::asNew(), 500, 0);
    }

    public function addFuel(int $fuelCount): bool {
        if ($this->capacity >= $this->storageAmount) return false;

        $fuelAmount = 10 * $fuelCount;
        $this->storageAmount += $fuelAmount;

        if ($this->storageAmount >= $this->capacity) {
            $this->storageAmount = $this->capacity;
            $event = new FuelTankBecameFullEvent($this->belongGameId, $this->tankId);
            $event->call();
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