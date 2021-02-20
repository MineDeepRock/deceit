<?php


namespace deceit\pmmp\services;


use deceit\dao\PlayerStatusDAO;
use deceit\services\SelectWolfPlayersService;
use deceit\services\StartGameService;
use deceit\storages\GameStorage;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class StartGamePMMPService
{
    static function execute(Player $owner): void {
        $gameId = PlayerStatusDAO::findByName($owner->getName())->getBelongGameId();
        if ($gameId === null) return;

        $selectWolfResult = SelectWolfPlayersService::execute($owner->getName(), $gameId);
        if (!$selectWolfResult) return;

        $startResult = StartGameService::execute($owner->getName(), $gameId);
        if (!$startResult) return;


        $game = GameStorage::findById($gameId);
        $map = $game->getMap();
        $level = Server::getInstance()->getLevelByName($map->getLevelName());
        foreach ($game->getPlayersName() as $playerName) {
            //初期位置にテレポート //TODO:ランダムな場所にテレポートするように
            $player = Server::getInstance()->getPlayer($playerName);
            $player->teleport(new Position($map->getStartVector(), $level));


            //役職のメッセージ
            if (in_array($playerName, $game->getWolfNameList())) {
                $player->sendMessage(TextFormat::RED . "あなたは人狼です");
                $player->sendMessage("市民を全員殺すか、タイムアップまで持ちこたえましょう");

                $player->sendTitle(TextFormat::RED . "あなたは人狼です", "市民を全員殺すか、タイムアップまで持ちこたえましょう");
            } else {
                $player->sendMessage(TextFormat::GREEN . "あなたは人間です");
                $player->sendMessage("燃料を燃料タンクに集めましょう");

                $player->sendTitle(TextFormat::GREEN . "あなたは人間です", "燃料を燃料タンクに集めましょう");
            }
        }
    }
}