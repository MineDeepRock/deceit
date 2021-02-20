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

    static function asNew(int $capacity): FuelTank {
        return new FuelTank(FuelTankId::asNew(), $capacity, 0);
    }

    public function addFuel(int $value): void {
        $this->storageAmount += $value;

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