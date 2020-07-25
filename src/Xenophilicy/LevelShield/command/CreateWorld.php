<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;
use pocketmine\level\generator\Flat;
use pocketmine\level\generator\hell\Nether;
use pocketmine\level\generator\normal\Normal;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\LevelShield;


/**
 * Class CreateWorld
 * @package Xenophilicy\LevelShield\command
 */
class CreateWorld extends SubCommand {
    
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
        if(!is_null(LevelShield::getInstance()->getWorldManager()->getWorld($name))){
            $sender->sendMessage(TF::RED . "A world with that name already exists");
            return false;
        }
        $generator = array_shift($args);
        switch($generator){
            case null:
            case "":
            case "normal":
                $generator = Normal::class;
                break;
            case "flat":
                $generator = Flat::class;
                break;
            case "nether":
                $generator = Nether::class;
                break;
            default:
                $sender->sendMessage(TF::RED . "That generator doesn't exist");
                return false;
        }
        $seed = mt_rand((int)((time() ^ 2) / 2), (int)((time() ^ 2) / 2));
        if(!LevelShield::getInstance()->getServer()->generateLevel($name, $seed, $generator)){
            $sender->sendMessage(TF::RED . "Failed to create world");
            return false;
        }
        LevelShield::getInstance()->getServer()->loadLevel($name);
        LevelShield::getInstance()->getWorldManager()->registerWorld($name);
        $sender->sendMessage(TF::GREEN . "World created with name " . TF::AQUA . $name);
        return true;
    }
}