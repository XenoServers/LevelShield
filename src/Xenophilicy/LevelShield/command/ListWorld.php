<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;


/**
 * Class ListWorld
 * @package Xenophilicy\LevelShield\command
 */
class ListWorld extends SubCommand {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("levelshield.list")){
            $sender->sendMessage(TF::RED . "You don't have permission to view worlds");
            return false;
        }
        $allworlds = LevelShield::getInstance()->getWorldManager()->getWorlds();
        $sender->sendMessage(TF::AQUA . "--- All worlds ---");
        foreach($allworlds as $world){
            $sender->sendMessage(TF::GREEN . "- [" . $world->getName() . "]");
            $flags = $world->getFlags();
            $sender->sendMessage(TF::YELLOW . "  Flags:");
            foreach($flags as $flag => $value){
                if($value){
                    $sender->sendMessage(TF::GOLD . "   {$flag}: " . TF::GREEN . "true");
                }else{
                    $sender->sendMessage(TF::GOLD . "   {$flag}: " . TF::RED . "false");
                }
            }
        }
        return true;
    }
}