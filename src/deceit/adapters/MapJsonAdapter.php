<?php

namespace deceit\adapters;


use deceit\models\FuelTankMapData;
use deceit\models\Map;
use pocketmine\math\Vector3;

class MapJsonAdapter
{
    static function decode(array $json): Map {
        $fuelTankMapDataList = [];
        foreach ($json["fuel_tanks"] as $fuelTank) {
            $fuelTankVectors[] = new FuelTankMapData($fuelTank["capacity"], new Vector3($fuelTank["x"], $fuelTank["y"], $fuelTank["z"]));
        }


        $fuelSpawnVectors = [];
        foreach ($json["fuel_spawn_vectors"] as $fuelSpawnVector) {
            $fuelSpawnVectors[] = new Vector3($fuelSpawnVector["x"], $fuelSpawnVector["y"], $fuelSpawnVector["z"]);
        }

        $startVector = new Vector3(
            $json["start_vector"]["x"],
            $json["start_vector"]["y"],
            $json["start_vector"]["z"],
        );

        $exitVector = new Vector3(
            $json["exit_vector"]["y"],
            $json["exit_vector"]["x"],
            $json["exit_vector"]["z"],
        );

        return new Map($json["level_name"], $json["name"], $startVector, $exitVector, $json["original_exit_block_id"], $fuelTankMapDataList, $fuelSpawnVectors);
    }

    static function encode(Map $map): array {

        $fuelTanks = [];
        foreach ($map->getFuelTankMapDataList() as $fueLTankMapData) {
            $fuelTanks[] = [
                "capacity" => $fueLTankMapData->getCapacity(),
                "x" => $fueLTankMapData->getVector()->getX(),
                "y" => $fueLTankMapData->getVector()->getY(),
                "z" => $fueLTankMapData->getVector()->getZ()
            ];
        }

        $fuelSpawnVectors = [];
        foreach ($map->getFuelSpawnVectors() as $fuelSpawnVector) {
            $fuelSpawnVectors[] = [
                "x" => $fuelSpawnVector->getX(),
                "y" => $fuelSpawnVector->getY(),
                "z" => $fuelSpawnVector->getZ(),
            ];
        }

        $startVector = [
            "x" => $map->getStartVector()->getX(),
            "y" => $map->getStartVector()->getY(),
            "z" => $map->getStartVector()->getZ(),
        ];
        $exitVector = [
            "x" => $map->getExitVector()->getX(),
            "y" => $map->getExitVector()->getY(),
            "z" => $map->getExitVector()->getZ(),
        ];

        return [
            "level_name" => $map->getLevelName(),
            "name" => $map->getName(),
            "start_vector" => $startVector,
            "exit_vector" => $exitVector,
            "original_exit_block_id" => $map->getOriginalExitBlockId(),
            "fuel_tanks" => $fuelTanks,
            "fuel_spawn_vectors" => $fuelSpawnVectors,
        ];
    }
}