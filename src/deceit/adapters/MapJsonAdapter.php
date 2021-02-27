<?php

namespace deceit\adapters;


use deceit\models\Map;
use pocketmine\math\Vector3;

class MapJsonAdapter
{
    static function decode(array $json): Map {
        $fuelTankVectors = [];
        foreach ($json["fuel_tank_vectors"] as $tankVector) {
            $fuelTankVectors[] = new Vector3($tankVector["x"], $tankVector["y"], $tankVector["z"]);
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

        return new Map($json["level_name"], $json["name"], $startVector, $exitVector, $json["original_exit_block_id"], $fuelTankVectors, $fuelSpawnVectors);
    }

    static function encode(Map $map): array {

        $fuelTankVectors = [];
        foreach ($map->getFuelTankVectors() as $tankVector) {
            $fuelTankVectors[] = [
                "x" => $tankVector->getX(),
                "y" => $tankVector->getY(),
                "z" => $tankVector->getZ()
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
            "fuel_tank_vectors" => $fuelTankVectors,
            "fuel_spawn_vectors" => $fuelSpawnVectors,
        ];
    }
}