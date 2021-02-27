<?php


namespace deceit\pmmp\services;


use bossbar_system\BossBar;
use bossbar_system\model\BossBarType;
use deceit\dao\PlayerStatusDAO;
use deceit\pmmp\BossBarTypeList;
use deceit\pmmp\scoreboards\GameSettingsScoreboard;
use deceit\services\SelectWolfPlayersService;
use deceit\services\StartGameService;
use deceit\storages\GameStorage;
use deceit\storages\PlayerDataOnGameStorage;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class StartGamePMMPService
{
    static function execute(Player $owner): bool {
        $gameId = PlayerStatusDAO::findByName($owner->getName())->getBelongGameId();
        if ($gameId === null) return false;

        $selectWolfResult = SelectWolfPlayersService::execute($owner->getName(), $gameId);
        if (!$selectWolfResult) return false;

        $startResult = StartGameService::execute($owner->getName(), $gameId);
        if (!$startResult) return false;


        $game = GameStorage::findById($gameId);
        $map = $game->getMap();
        $level = Server::getInstance()->getLevelByName($map->getLevelName());
        foreach ($game->getPlayersName() as $playerName) {
            //初期位置にテレポート //TODO:ランダムな場所にテレポートするように
            $player = Server::getInstance()->getPlayer($playerName);
            $player->teleport(new Position($map->getStartVector(), $level));
            $player->getInventory()->setContents([]);

            //スコアボード
            GameSettingsScoreboard::delete($player);

            //TODO:ボスバーのメッセージ
            //役職のメッセージ
            $playerDataOnGame = PlayerDataOnGameStorage::findByName($playerName);
            if ($playerDataOnGame->isWolf()) {
                $bossBar = new BossBar($player, BossBarTypeList::GameTimer(), TextFormat::RED . "", 0.0);
                $bossBar->send();

                $wolfNemListAsString = "あなた ";
                foreach (PlayerDataOnGameStorage::getWolfs($gameId) as $wolfDataOnGame) $wolfNemListAsString .= $wolfDataOnGame->getName() . " ";

                $player->sendMessage(TextFormat::RED . $wolfNemListAsString . TextFormat::RED . "が人狼です");
                $player->sendMessage("市民を全員殺すか、タイムアップまで持ちこたえましょう");

                $player->sendTitle(TextFormat::RED . "あなたは人狼です", "市民を全員殺すか、タイムアップまで持ちこたえましょう");
            } else {
                $bossBar = new BossBar($player, BossBarTypeList::GameTimer(), TextFormat::GREEN . "", 0.0);
                $bossBar->send();

                $player->sendMessage(TextFormat::GREEN . "あなたは人間です");
                $player->sendMessage("燃料を燃料タンクに集めましょう");

                $player->sendTitle(TextFormat::GREEN . "あなたは人間です", "燃料を燃料タンクに集めましょう");
            }
        }

        return true;
    }
}