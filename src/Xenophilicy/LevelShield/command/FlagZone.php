<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;


/**
 * Class FlagZone
 * @package Xenophilicy\LevelShield\command
 */
class FlagZone extends SubCommand {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("levelshield.flag")){
            $sender->sendMessage(TF::RED . "You don't have permission to edit zone flags");
            return false;
        }
        $name = array_shift($args);
        if($name === null || trim($name) === ""){
            $sender->sendMessage(TF::RED . "Specify a zone to edit flags for");
            return false;
        }
        if(is_null($zone = LevelShield::getInstance()->getZoneManager()->getZone($name))){
            $sender->sendMessage(TF::RED . "That zone doesn't exist");
            return false;
        }
        if(count($args) === 0 && $sender instanceof Player){
            LevelShield::$formCache[$sender->getName()] = ["type" => "zone", "name" => $zone->getName()];
            LevelShield::getInstance()->flagForm($sender);
            return true;
        }
        if(count($args) < 2){
            $sender->sendMessage(TF::RED . "Usage: /zone flag <zone> <flag> <value>");
            return false;
        }
        $flag = array_shift($args);
        if(!in_array($flag, ["interact", "damage", "pvp", "edit", "fly", "drop", "hunger", "explode", "bow", "burn", "effects"])){
            $sender->sendMessage(TF::RED . "Available flags: " . TF::YELLOW . "interact, damage, pvp, edit, fly, drop, hunger, explode, bow, burn, effects");
            return false;
        }
        $value = array_shift($args);
        if($value === "true"){
            $zone->setFlag($flag, true);
        }elseif($value === "false"){
            $zone->setFlag($flag, false);
        }else{
            $sender->sendMessage(TF::RED . "Flags can be set to true or false");
            return false;
        }
        $sender->sendMessage(TF::GREEN . "Flag " . $flag . " updated to " . $value);
        return true;
    }
}