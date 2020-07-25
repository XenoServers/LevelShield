<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;


/**
 * Class CreateZone
 * @package Xenophilicy\LevelShield\command
 */
class CreateZone extends SubCommand {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("levelshield.create")){
            $sender->sendMessage(TF::RED . "You don't have permission to create zones");
            return false;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(TF::RED . "You can only create zones in-game");
            return false;
        }
        $name = array_shift($args);
        if($name === null || empty(trim($name))){
            $sender->sendMessage(TF::RED . "Enter a zone name");
            return false;
        }
        if(isset(LevelShield::$creation[$sender->getName()])){
            $sender->sendMessage(TF::RED . "Finish your current zone before starting another");
            return false;
        }
        $name = strtolower($name);
        if(!is_null(LevelShield::getInstance()->getZoneManager()->getZone($name))){
            $sender->sendMessage(TF::RED . "A zone with that name already exists");
            return false;
        }
        LevelShield::$creation[$sender->getName()] = [$name, false, false];
        $sender->sendMessage(TF::YELLOW . "Tap a block to set position 1");
        return true;
    }
}