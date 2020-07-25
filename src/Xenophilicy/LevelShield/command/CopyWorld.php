<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;
use Xenophilicy\LevelShield\WorldUtils;


/**
 * Class CopyWorld
 * @package Xenophilicy\LevelShield\command
 */
class CopyWorld extends SubCommand {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("levelshield.manageworlds")){
            $sender->sendMessage(TF::RED . "You don't have permission to manage worlds");
            return false;
        }
        $target = array_shift($args);
        if($target === null || empty(trim($target))){
            $sender->sendMessage(TF::RED . "Enter a target world name");
            return false;
        }
        if(is_null($oldworld = LevelShield::getInstance()->getWorldManager()->getWorld($target))){
            $sender->sendMessage(TF::RED . "That world doesn't exist");
            return false;
        }
        $new = array_shift($args);
        if($new === null || empty(trim($new))){
            $sender->sendMessage(TF::RED . "Enter a destination world name");
            return false;
        }
        if($target === $new){
            $sender->sendMessage(TF::RED . "Target world name cannot be the same as the new world name");
            return false;
        }
        $targetPath = LevelShield::getInstance()->getWorldFolderPath(LevelShield::getInstance()->getServer()->getLevelByName($target)->getFolderName());
        $newPath = LevelShield::getInstance()->getWorldFolderPath($new);
        WorldUtils::copy($targetPath, $newPath);
        if(!WorldUtils::checkData($sender, $newPath, $new)) return false;
        $world = LevelShield::getInstance()->getWorldManager()->registerWorld($new);
        $world->setFlags($oldworld->getFlags());
        $sender->sendMessage(TF::GREEN . "World copied as " . TF::AQUA . $new);
        return true;
    }
}