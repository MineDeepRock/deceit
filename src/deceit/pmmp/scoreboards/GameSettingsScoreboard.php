<?php


namespace deceit\pmmp\scoreboards;


use deceit\dao\PlayerStatusDAO;
use deceit\storages\GameStorage;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use scoreboard_builder\Score;
use scoreboard_builder\Scoreboard;
use scoreboard_builder\ScoreboardSlot;
use scoreboard_builder\ScoreSortType;

class GameSettingsScoreboard extends Scoreboard
{

    static function create(Player $player): Scoreboard {
        $playerStatus = PlayerStatusDAO::findByName($player->getName());
        $game = GameStorage::findById($playerStatus->getBelongGameId());
        if ($game === null) {
            //TODO:error
        }

        $playerCount = $game->getMaxPlayers() . "/" . count($game->getPlayersName());

        $scores = [
            new Score(TextFormat::RESET . "----------------------"),
            new Score(TextFormat::BOLD . TextFormat::YELLOW . "ゲーム情報:"),
            new Score(TextFormat::BOLD . "> 主催者:{$game->getGameOwnerName()}"),
            new Score(TextFormat::BOLD . "> プレイヤー数:{$playerCount}"),
            new Score(TextFormat::BOLD . "> 人狼:{$game->getWolfsCount()}"),
            new Score(TextFormat::BOLD . "> マップ:{$game->getMap()->getName()}"),
            new Score("----------------------"),
        ];
        return parent::__create(ScoreboardSlot::sideBar(), "Deceit", $scores, ScoreSortType::smallToLarge());
    }

    static function send(Player $player) {
        $scoreboard = self::create($player);
        parent::__send($player, $scoreboard);
    }

    static function update(Player $player) {
        $scoreboard = self::create($player);
        parent::__update($player, $scoreboard);
    }
}