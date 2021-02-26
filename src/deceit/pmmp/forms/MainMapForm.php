<?php


namespace deceit\pmmp\forms;


use deceit\dao\MapDAO;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;

class MainMapForm extends SimpleForm
{

    public function __construct() {
        $buttons = [new SimpleFormButton(
            "マップを追加",
            null,
            function (Player $player) {
                $player->sendForm(new CreateNewMapForm());
            }
        )];

        foreach (MapDAO::all() as $map) {
            $buttons[] = new SimpleFormButton(
                $map->getName(),
                null,
                function (Player $player) {
                }
            );
        }

        parent::__construct("", "", [

        ]);
    }

    function onClickCloseButton(Player $player): void {
    }
}