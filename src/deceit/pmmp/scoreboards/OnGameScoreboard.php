<?php


namespace deceit\pmmp\scoreboards;


use deceit\dao\PlayerDataDAO;
use deceit\models\Game;
use deceit\storages\GameStorage;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use scoreboard_builder\Score;
use scoreboard_builder\Scoreboard;
use scoreboard_builder\ScoreboardSlot;
use scoreboard_builder\ScoreSortType;

class OnGameScoreboard extends Scoreboard
{

    static function create(Game $game): Scoreboard {
        $scores = [
            new Score(TextFormat::RESET . "----------------------"),
            new Score("参加者一覧:")
        ];

        foreach ($game->getPlayerNameList() as $playerName) $scores[] = new Score(">" . $playerName);
        $scores[] = new Score(TextFormat::RESET . "----------------------");


        return parent::__create(ScoreboardSlot::sideBar(), "マップ:{$game->getMap()->getName()}", $scores, ScoreSortType::smallToLarge());
    }

    static function send(Player $player, Game $game) {
        $scoreboard = self::create($game);
        parent::__send($player, $scoreboard);
    }

    static function update(Player $player, Game $game) {
        $scoreboard = self::create($game);
        parent::__update($player, $scoreboard);
    }
}