<?php


namespace deceit\models;


use pocketmine\math\Vector3;

class Map
{
    private string $levelName;
    private string $name;

    private Vector3 $startVector;
    private Vector3 $exitVector;
    private int $originalExitBlockId;
    private array $fuelTankMapDataList;
    private array $fuelSpawnVectors;

    private array $itemDataOnMapList;
    private array $gunDataOnMapList;

    public function __construct(string $levelName, string $name, Vector3 $startVector, Vector3 $exitVector, int $originalExitBlockId, array $fuelTankMapDataList, array $fuelSpawnVectors, array $itemDataOnMapList, array $gunDataOnMapList) {
        $this->levelName = $levelName;
        $this->name = $name;
        $this->startVector = $startVector;
        $this->exitVector = $exitVector;
        $this->originalExitBlockId = $originalExitBlockId;
        $this->fuelTankMapDataList = $fuelTankMapDataList;
        $this->fuelSpawnVectors = $fuelSpawnVectors;
        $this->itemDataOnMapList = $itemDataOnMapList;
        $this->gunDataOnMapList = $gunDataOnMapList;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return Vector3
     */
    public function getStartVector(): Vector3 {
        return $this->startVector;
    }

    /**
     * @return FuelTankMapData[]
     */
    public function getFuelTankMapDataList(): array {
        return $this->fuelTankMapDataList;
    }

    /**
     * @return Vector3[]
     */
    public function getFuelSpawnVectors(): array {
        return $this->fuelSpawnVectors;
    }

    /**
     * @return string
     */
    public function getLevelName(): string {
        return $this->levelName;
    }

    /**
     * @return Vector3
     */
    public function getExitVector(): Vector3 {
        return $this->exitVector;
    }

    /**
     * @return int
     */
    public function getOriginalExitBlockId(): int {
        return $this->originalExitBlockId;
    }

    /**
     * @return GunDataOnMap[]
     */
    public function getGunDataOnMapList(): array {
        return $this->gunDataOnMapList;
    }

    /**
     * @return ItemDataOnMap[]
     */
    public function getItemDataOnMapList(): array {
        return $this->itemDataOnMapList;
    }
}