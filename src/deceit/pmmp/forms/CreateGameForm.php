<?php


namespace deceit\pmmp\forms;


use deceit\dao\MapDAO;
use deceit\services\CreateGameService;
use form_builder\models\custom_form_elements\Dropdown;
use form_builder\models\custom_form_elements\Input;
use form_builder\models\custom_form_elements\Slider;
use form_builder\models\CustomForm;
use pocketmine\Player;
use pocketmine\scheduler\TaskScheduler;

class CreateGameForm extends CustomForm
{
    private TaskScheduler $scheduler;

    private Dropdown $mapNameElement;
    private Slider $maxPlayersElement;
    private Slider $wolfsCountElement;

    public function __construct(TaskScheduler $scheduler) {
        $this->scheduler = $scheduler;

        $mapNames = [];
        foreach (MapDAO::all() as $map) {
            $mapNames[] = $map->getName();
        }

        $this->mapNameElement = new Dropdown("マップ", $mapNames);
        $this->maxPlayersElement = new Slider("最大プレイヤー数", 3, 10, 6);
        $this->wolfsCountElement = new Slider("人狼の数", 1, 3, 1);

        parent::__construct(
            "ゲームを作成",
            [
                $this->mapNameElement,
                $this->maxPlayersElement,
                $this->wolfsCountElement,
            ]
        );
    }

    function onSubmit(Player $player): void {
        $mapName = $this->mapNameElement->getResult();
        $maxPlayers = intval($this->maxPlayersElement->getResult());
        $wolfCount = intval($this->wolfsCountElement->getResult());

        $result = CreateGameService::execute($player->getName(), $mapName, $maxPlayers, $wolfCount, $this->scheduler);
        if ($result) {
            $player->sendMessage("ゲームを作成しました");
        } else {
            $player->sendMessage("ゲームを作成できませんでした");
        }
    }

    function onClickCloseButton(Player $player): void { }
}