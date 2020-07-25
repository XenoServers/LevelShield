<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;


/**
 * Class UnloadWorld
 * @package Xenophilicy\LevelShield\command
 */
class UnloadWorld extends SubCommand {
    
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
        if(!LevelShield::getInstance()->getServer()->isLevelLoaded($name)){
            $sender->sendMessage(TF::RED . "That world is already unloaded");
            return false;
        }
        $level = LevelShield::getInstance()->getServer()->getLevelByName($name);
        LevelShield::getInstance()->getServer()->unloadLevel($level, true);
        $sender->sendMessage(TF::GREEN . "World unloaded");
        return true;
    }
}