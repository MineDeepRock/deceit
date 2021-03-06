<?php

namespace deceit\pmmp\utilities;


class GetWorldNameList
{
    static function execute(): array {
        return array_values(array_diff(scandir("./worlds/"), [".", ".."]));
    }
}