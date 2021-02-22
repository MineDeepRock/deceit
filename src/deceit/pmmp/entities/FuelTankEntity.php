<?php


namespace deceit\pmmp\entities;


use deceit\models\FuelTankId;
use deceit\models\GameId;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;

class FuelTankEntity extends EntityBase
{
    private GameId $belongGameId;
    private FuelTankId $fuelTankId;

    const NAME = "FuelTank";

    public string $geometryId = "geometry." . self::NAME;
    public string $geometryName = self::NAME . ".geo.json";

    public function __construct(Level $level, Position $position,GameId $belongGameId, FuelTankId $fuelTankId) {
        $this->belongGameId = $belongGameId;
        $this->fuelTankId = $fuelTankId;

        parent::__construct($level, $nbt = new CompoundTag('', [
            'Pos' => new ListTag('Pos', [
                new DoubleTag('', $position->getX()),
                new DoubleTag('', $position->getY()),
                new DoubleTag('', $position->getZ())
            ]),
            'Motion' => new ListTag('Motion', [
                new DoubleTag('', 0),
                new DoubleTag('', 0),
                new DoubleTag('', 0)
            ]),
            'Rotation' => new ListTag('Rotation', [
                new FloatTag("", 0),
                new FloatTag("", 0)
            ]),
        ]));
    }

    /**
     * @return FuelTankId
     */
    public function getTankId(): FuelTankId {
        return $this->fuelTankId;
    }

    /**
     * @return GameId
     */
    public function getBelongGameId(): GameId {
        return $this->belongGameId;
    }

}