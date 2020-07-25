<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;


/**
 * Class TeleportWorld
 * @package Xenophilicy\LevelShield\command
 */
class TeleportWorld extends SubCommand {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("levelshield.tp")){
            $sender->sendMessage(TF::RED . "You don't have permission to teleport to worlds");
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
        LevelShield::getInstance()->getServer()->loadLevel($name);
        $level = LevelShield::getInstance()->getServer()->getLevelByName($name);
        $target = array_shift($args);
        if($target === null || empty(trim($target))){
            if($sender instanceof Player){
                $sender->teleport($level->getSpawnLocation());
                $sender->sendMessage(TF::GREEN . "You've been teleported to " . TF::AQUA . $name);
                return true;
            }else{
                $sender->sendMessage(TF::RED . "You must specify a player");
                return false;
            }
        }else{
            $player = LevelShield::getInstance()->getServer()->getPlayer($target);
            if($player instanceof Player){
                $player->teleport($level->getSpawnLocation());
                $player->sendMessage(TF::GREEN . "You've been teleported to " . TF::AQUA . $name);
                if($sender === $player) return true;
                $sender->sendMessage(TF::YELLOW . $player->getName() . " has been teleported to " . TF::AQUA . $name);
                return true;
            }else{
                $sender->sendMessage(TF::RED . "That player doesn't exist");
                return false;
            }
        }
    }
}