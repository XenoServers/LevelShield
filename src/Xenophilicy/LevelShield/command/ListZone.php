<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;


/**
 * Class ListZone
 * @package Xenophilicy\LevelShield\command
 */
class ListZone extends SubCommand {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("levelshield.list")){
            $sender->sendMessage(TF::RED . "You don't have permission to view zones");
            return false;
        }
        $level = array_shift($args);
        $allzones = LevelShield::getInstance()->getZoneManager()->getZones();
        if($level === null || empty(trim($level))){
            if(count($allzones) === 0){
                $sender->sendMessage(TF::RED . "There are no zones to view");
                return false;
            }
            $sender->sendMessage(TF::AQUA . "--- All zones ---");
            $this->listZones($allzones, $sender);
        }else{
            $level = LevelShield::getInstance()->getServer()->getLevelByName($level);
            if(is_null($level)){
                $sender->sendMessage(TF::RED . "That world doesn't exist");
                return false;
            }
            $zones = [];
            foreach($allzones as $zone){
                if($zone->getLevel()->getName() !== $level->getName()) continue;
                array_push($zones, $zone);
            }
            if(count($zones) === 0){
                $sender->sendMessage(TF::RED . "There are no zones on that world");
                return false;
            }
            $sender->sendMessage(TF::AQUA . "--- Zones on " . $level->getName() . " ---");
            $this->listZones($zones, $sender, false);
        }
        return true;
    }
    
    /**
     * @param array $zones
     * @param CommandSender $sender
     * @param bool $showLevel
     */
    private function listZones(array $zones, CommandSender $sender, $showLevel = true){
        foreach($zones as $zone){
            $sender->sendMessage(TF::GREEN . "- [" . $zone->getName() . "]");
            if($showLevel) $sender->sendMessage(TF::YELLOW . "  Level: " . $zone->getLevel()->getName());
        }
    }
}