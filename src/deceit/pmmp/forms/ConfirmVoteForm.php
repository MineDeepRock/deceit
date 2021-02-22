<?php


namespace deceit\pmmp\forms;


use deceit\pmmp\entities\CadaverEntity;
use form_builder\models\modal_form_elements\ModalFormButton;
use form_builder\models\ModalForm;
use pocketmine\Player;

class ConfirmVoteForm extends ModalForm
{

    private CadaverEntity $cadaverEntity;

    public function __construct(CadaverEntity $cadaverEntity) {
        $this->cadaverEntity = $cadaverEntity;
        parent::__construct(
            $cadaverEntity->getOwner()->getName() . "に投票しますか",
            "",
            new ModalFormButton("投票する"),
            new ModalFormButton("キャンセル"),
        );
    }

    function onClickCloseButton(Player $player): void { }

    public function onClickButton1(Player $player): void {
        $this->cadaverEntity->vote($player->getName());
    }

    public function onClickButton2(Player $player): void { }
}