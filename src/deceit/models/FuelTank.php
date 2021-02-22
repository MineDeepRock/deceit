<?php


namespace deceit\models;


class FuelTank
{
    private FuelTankId $tankId;

    private int $capacity;
    private int $storageAmount;

    private function __construct(FuelTankId $tankId, int $capacity, int $storageAmount) {
        $this->tankId = $tankId;
        $this->capacity = $capacity;
        $this->storageAmount = $storageAmount;
    }

    static function asNew(): FuelTank {
        return new FuelTank(FuelTankId::asNew(), 500, 0);
    }

    public function addFuel(int $fuelCount): bool {
        if ($this->capacity >= $this->storageAmount) return false;

        $fuelAmount = 10 * $fuelCount;
        $this->storageAmount += $fuelAmount;

        if ($this->storageAmount >= $this->capacity) {
            //TODO: event
            $this->storageAmount = $this->capacity;
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

    public function getAmountPercentage() : float {
        if ($this->storageAmount === 0) return 0;
        return $this->storageAmount / $this->capacity;
    }
}