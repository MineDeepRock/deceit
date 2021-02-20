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

    public function addFuel(int $fuelCount): void {
        $fuelAmount = 10 * $fuelCount;

        $this->storageAmount += $fuelAmount;

        if ($this->storageAmount >= $this->capacity) {
            //TODO: event
            $this->storageAmount = $this->capacity;
        }
    }

    public function reduce(int $value): void {
        $this->storageAmount -= $value;

        if ($this->storageAmount < 0) $this->storageAmount = 0;
    }
}