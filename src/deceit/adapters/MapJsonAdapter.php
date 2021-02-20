<?php

namespace deceit\adapters;


use deceit\models\Map;
use pocketmine\math\Vector3;

class MapJsonAdapter
{
    static function decode(array $json): Map {
        $fuelTankVectors = [];
        foreach ($json["fuel_tank_vectors"] as $tankVector) {
            $fuelTankVectors[] = [
                new Vector3($tankVector["x"], $tankVector["y"], $tankVector["z"],)
            ];
        }


        $fuelSpawnVectors = [];
        foreach ($json["fuel_spawn_vectors"] as $fuelSpawnVector) {
            $fuelSpawnVectors[] = [
                new Vector3($fuelSpawnVector["x"], $fuelSpawnVector["y"], $fuelSpawnVector["z"],)
            ];
        }

        $startVector = new Vector3(
            $json["start_vector"]["x"],
            $json["start_vector"]["y"],
            $json["start_vector"]["z"],
        );

        return new Map($json["level_name"], $json["name"], $startVector, $fuelTankVectors, $fuelSpawnVectors);
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
            $fuelTankVectors[] = [
                "x" => $fuelSpawnVector->getX(),
                "y" => $fuelSpawnVector->getY(),
                "z" => $fuelSpawnVector->getZ(),
            ];
        }


        return [
            "level_name" => $map->getLevelName(),
            "name" => $map->getName(),
            "start_vector" => $map->getStartVector(),
            "fuel_tank_vectors" => $fuelTankVectors,
            "fuel_spawn_vectors" => $fuelSpawnVectors,
        ];
    }
}