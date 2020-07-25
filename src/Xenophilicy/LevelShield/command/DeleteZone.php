<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;


/**
 * Class DeleteZone
 * @package Xenophilicy\LevelShield\command
 */
class DeleteZone extends SubCommand {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("levelshield.delete")){
            $sender->sendMessage(TF::RED . "You don't have permission to delete zones");
            return false;
        }
        $name = array_shift($args);
        if($name === null || empty(trim($name))){
            $sender->sendMessage(TF::RED . "Enter a zone name");
            return false;
        }
        $name = strtolower($name);
        if(is_null(LevelShield::getInstance()->getZoneManager()->getZone($name))){
            $sender->sendMessage(TF::RED . "That zone doesn't exist");
            return false;
        }
        LevelShield::getInstance()->getZoneManager()->deleteZone($name);
        $sender->sendMessage(TF::GREEN . "Zone deleted");
        return true;
    }
}