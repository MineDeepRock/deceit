<?php


namespace deceit\pmmp\entities;


use deceit\pmmp\items\MedicineKitItem;

class MedicineKitOnMapEntity extends ItemOnMapEntity
{
    const NAME = MedicineKitItem::Name;

    public string $skinName = self::NAME;
    public string $geometryId = "geometry." . self::NAME;
    public string $geometryName = self::NAME . ".geo.json";
}