<?php


namespace deceit\models;


use pocketmine\math\Vector3;

class Map
{
    private string $levelName;
    private string $name;

    private Vector3 $startVector;
    private array $fuelTankVectors;
    private array $fuelSpawnVectors;

    public function __construct(string $levelName, string $name, Vector3 $startVector, array $fuelTankVectors, array $fuelSpawnVectors) {
        $this->levelName = $levelName;
        $this->name = $name;
        $this->startVector = $startVector;
        $this->fuelTankVectors = $fuelTankVectors;
        $this->fuelSpawnVectors = $fuelSpawnVectors;
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
     * @return Vector3[]
     */
    public function getFuelTankVectors(): array {
        return $this->fuelTankVectors;
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
}