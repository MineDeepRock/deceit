<?php


namespace deceit\pmmp\services;


use bossbar_system\BossBar;
use deceit\dao\PlayerDataDAO;
use deceit\pmmp\BossBarTypeList;
use deceit\pmmp\items\TransformItem;
use deceit\pmmp\scoreboards\GameSettingsScoreboard;
use deceit\pmmp\scoreboards\OnGameScoreboard;
use deceit\services\StartGameService;
use deceit\storages\GameStorage;
use pocketmine\Player;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class StartGamePMMPService
{
    static function execute(Player $owner, TaskScheduler $taskScheduler): bool {
        $gameId = PlayerDataDAO::findByName($owner->getName())->getBelongGameId();
        if ($gameId === null) return false;

        $startResult = StartGameService::execute($owner->getName(), $gameId, $taskScheduler);
        if (!$startResult) return false;


        $game = GameStorage::findById($gameId);
        $map = $game->getMap();
        $level = Server::getInstance()->getLevelByName($map->getLevelName());

        SpawnItemPMMPService::execute($map);
        SpawnBloodPackPMMPService::execute($map);

        foreach ($game->getPlayerNameList() as $playerName) {
            //初期位置にテレポート //TODO:ランダムな場所にテレポートするように
            $player = Server::getInstance()->getPlayer($playerName);
            $player->teleport($level->getSpawnLocation());
            $player->teleport($map->getStartVector());

            $player->getInventory()->setContents([]);

            //スコアボード
            GameSettingsScoreboard::delete($player);
            OnGameScoreboard::send($player, $game);

            //役職のメッセージ
            if (in_array($playerName, $game->getWolfNameList())) {
                $wolfNemListAsString = "あなた ";
                foreach ($game->getWolfNameList() as $wolfName) $wolfNemListAsString .= $wolfName . " ";

                $player->sendMessage(TextFormat::RED . $wolfNemListAsString . TextFormat::RED . "が人狼です");
                $player->sendMessage("市民を全員殺すか、タイムアップまで持ちこたえましょう");

                $player->sendTitle(TextFormat::RED . "あなたは人狼です", "市民を全員殺すか、タイムアップまで持ちこたえましょう");

                $player->getInventory()->sendContents([]);
                $player->getInventory()->addItem(new TransformItem());
            } else {
                $player->sendMessage(TextFormat::GREEN . "あなたは人間です");
                $player->sendMessage("燃料を燃料タンクに集めましょう");

                $player->sendTitle(TextFormat::GREEN . "あなたは人間です", "燃料を燃料タンクに集めましょう");

                //TODO:別のアイテムにする
                $player->getInventory()->addItem(new TransformItem());
            }
        }

        return true;
    }
}