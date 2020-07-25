<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;


/**
 * Class PriorityZone
 * @package Xenophilicy\LevelShield\command
 */
class PriorityZone extends SubCommand {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("levelshield.flag")){
            $sender->sendMessage(TF::RED . "You don't have permission to edit zone properties");
            return false;
        }
        if(count($args) < 2){
            $sender->sendMessage(TF::RED . "Usage: /zone priority <zone> <priority>");
            return false;
        }
        $name = array_shift($args);
        if(is_null($zone = LevelShield::getInstance()->getZoneManager()->getZone($name))){
            $sender->sendMessage(TF::RED . "That zone doesn't exist");
            return false;
        }
        $priority = intval(array_shift($args));
        if(!is_int($priority)){
            $sender->sendMessage(TF::RED . "The zone priority must be an integer");
            return false;
        }
        $zone->setPriority($priority);
        $sender->sendMessage(TF::GREEN . "Priority updated to " . $priority);
        return true;
    }
}