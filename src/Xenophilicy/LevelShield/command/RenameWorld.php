<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;
use Xenophilicy\LevelShield\WorldUtils;


/**
 * Class RenameWorld
 * @package Xenophilicy\LevelShield\command
 */
class RenameWorld extends SubCommand {
    
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
        if(is_null($oldworld = LevelShield::getInstance()->getWorldManager()->getWorld($target)) || is_null($oldlevel = (LevelShield::getInstance()->getServer()->getLevelByName($target)))){
            $sender->sendMessage(TF::RED . "That world doesn't exist");
            return false;
        }
        $new = array_shift($args);
        if($new === null || empty(trim($new))){
            $sender->sendMessage(TF::RED . "Enter a new world name");
            return false;
        }
        if(strtolower($target) === strtolower($new)){
            $sender->sendMessage(TF::RED . "You can't rename a world to its current name");
            return false;
        }
        $targetPath = LevelShield::getInstance()->getWorldFolderPath($oldlevel->getFolderName());
        $newPath = LevelShield::getInstance()->getWorldFolderPath($new);
        $level = LevelShield::getInstance()->getServer()->getLevelByName($target);
        if(LevelShield::getInstance()->getServer()->isLevelLoaded($target)){
            if(!(LevelShield::getInstance()->getServer()->unloadLevel($level, true))){
                $sender->sendMessage(TF::RED . "Failed to unload target world");
                return false;
            }
        }
        $world = LevelShield::getInstance()->getWorldManager()->registerWorld($new);
        $world->setFlags($oldworld->getFlags());
        LevelShield::getInstance()->getWorldManager()->deleteWorld($target);
        WorldUtils::copy($targetPath, $newPath);
        WorldUtils::delete($targetPath);
        if(!WorldUtils::checkData($sender, $newPath, $new)) return false;
        LevelShield::getInstance()->getServer()->loadLevel($new);
        $sender->sendMessage(TF::GREEN . "World " . TF::AQUA . $target . TF::GREEN . " renamed to " . TF::AQUA . $new);
        return true;
    }
}