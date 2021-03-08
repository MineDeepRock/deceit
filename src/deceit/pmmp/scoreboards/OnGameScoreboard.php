<?php


namespace deceit\pmmp\scoreboards;


use deceit\models\Game;
use deceit\storages\PlayerStatusStorage;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use scoreboard_builder\Score;
use scoreboard_builder\Scoreboard;
use scoreboard_builder\ScoreboardSlot;
use scoreboard_builder\ScoreSortType;

class OnGameScoreboard extends Scoreboard
{

    static function create(Player $player, Game $game): Scoreboard {
        $scores = [
            new Score(TextFormat::RESET . "----------------------"),
            new Score("参加者一覧:")
        ];
        if (in_array($player->getName(), $game->getWolfNameList())) {
            $status = PlayerStatusStorage::findByName($player->getName());

            foreach ($game->getPlayerNameList() as $playerName) {
                if (in_array($playerName, $game->getWolfNameList())) {
                    if ($status->getBloodTank() === 0) {
                        $bloodGaugeAsString = str_repeat(TextFormat::WHITE . "■", 5);

                    } else if ($status->canTransform()) {
                        $bloodGaugeAsString = str_repeat(TextFormat::RED . "■", $status->getBloodTank());

                    } else {
                        $bloodGaugeAsString = str_repeat(TextFormat::RED . "■", $status->getBloodTank());
                        $bloodGaugeAsString .= str_repeat(TextFormat::WHITE . "■", 5 - $status->getBloodTank());
                    }

                    $scores[] = new Score(">" . $playerName . $bloodGaugeAsString);

                } else {
                    $scores[] = new Score(">" . $playerName);

                }
            }

        } else {
            foreach ($game->getPlayerNameList() as $playerName) $scores[] = new Score(">" . $playerName);
        }

        $scores[] = new Score(TextFormat::RESET . "----------------------");


        return parent::__create(ScoreboardSlot::sideBar(), "マップ:{$game->getMap()->getName()}", $scores, ScoreSortType::smallToLarge());
    }

    static function send(Player $player, Game $game) {
        $scoreboard = self::create($player, $game);
        parent::__send($player, $scoreboard);
    }

    static function update(Player $player, Game $game) {
        $scoreboard = self::create($player, $game);
        parent::__update($player, $scoreboard);
    }
}