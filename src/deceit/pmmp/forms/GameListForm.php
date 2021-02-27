<?php


namespace deceit\pmmp\forms;


use deceit\pmmp\services\JoinGamePMMPService;
use deceit\storages\GameStorage;
use form_builder\models\simple_form_elements\SimpleFormButton;
use form_builder\models\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class GameListForm extends SimpleForm
{

    public function __construct(Player $player) {
        $availableGamesAsButtons = [];
        $unavailableGamesAsButtons = [];


        foreach (GameStorage::getAll() as $game) {
            $gameId = $game->getGameId();
            $mapName = TextFormat::BOLD . $game->getMap()->getName() . TextFormat::RESET;
            $playersCountAsText = TextFormat::BOLD . count($game->getPlayerNameList()) . "/" . $game->getMaxPlayers() . TextFormat::RESET;
            $wolfCount = TextFormat::BOLD . $game->getWolfsCount() . TextFormat::RESET;
            $text = "マップ:{$mapName},人数:{$playersCountAsText},人狼:{$wolfCount},オーナー:{$game->getGameOwnerName()}";

            if ($game->canJoin($player->getName())) {
                $availableGamesAsButtons[] = new SimpleFormButton(
                    TextFormat::GREEN . "参加可能" . TextFormat::RESET . $text,
                    null,
                    function (Player $player) use ($gameId): void {
                        JoinGamePMMPService::execute($player, $gameId);
                    }
                );

            } else {
                $unavailableGamesAsButtons[] = new SimpleFormButton(
                    TextFormat::RED . "参加不可能" . TextFormat::RESET . $text,
                    null,
                    function (Player $player) {
                        $player->sendForm(new GameListForm($player));
                    }
                );

            }
        }

        parent::__construct("試合一覧", "タップで参加できます", array_merge($availableGamesAsButtons, $unavailableGamesAsButtons));
    }

    function onClickCloseButton(Player $player): void {
    }
}