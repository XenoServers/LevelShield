<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;
use Xenophilicy\LevelShield\WorldUtils;


/**
 * Class DeleteWorld
 * @package Xenophilicy\LevelShield\command
 */
class DeleteWorld extends SubCommand {
    
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
        $name = array_shift($args);
        if($name === null || empty(trim($name))){
            $sender->sendMessage(TF::RED . "Enter a world name");
            return false;
        }
        if(is_null(LevelShield::getInstance()->getWorldManager()->getWorld($name))){
            $sender->sendMessage(TF::RED . "That world doesn't exist");
            return false;
        }
        $level = LevelShield::getInstance()->getServer()->getLevelByName($name);
        if(LevelShield::getInstance()->getServer()->isLevelLoaded($name)){
            if(!(LevelShield::getInstance()->getServer()->unloadLevel($level, true))){
                $sender->sendMessage(TF::RED . "Failed to unload world");
                return false;
            }
        }
        LevelShield::getInstance()->getWorldManager()->deleteWorld($name);
        $directory = LevelShield::getInstance()->getWorldFolderPath($level->getFolderName());
        /** @noinspection PhpInternalEntityUsedInspection */
        LevelShield::getInstance()->getServer()->removeLevel($level);
        WorldUtils::delete($directory);
        $sender->sendMessage(TF::GREEN . "World deleted");
        return true;
    }
}