<?php


namespace deceit\pmmp;


use bossbar_system\model\BossBarType;

class BossBarTypeList
{
    static function GameTimer(): BossBarType {
        return new BossBarType("GameTimer");
    }

    static function ExitTimer(): BossBarType {
        return new BossBarType("ExitTimer");
    }

    static function Transform(): BossBarType {
        return new BossBarType("Transform");
    }
}