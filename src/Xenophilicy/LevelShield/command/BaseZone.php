<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use Xenophilicy\LevelShield\LevelShield;

/**
 * Class BaseZone
 * @package Xenophilicy\LevelShield\command
 */
class BaseZone extends Command implements PluginIdentifiableCommand {
    
    /** @var  SubCommand[] */
    protected $subCommands;
    
    public function __construct(){
        parent::__construct("zone", "Manage protection zones");
        $this->setPermission("levelshield");
        $this->setAliases(["area"]);
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(count($args) === 0 || !array_key_exists($args[0], $this->subCommands)){
            $sender->sendMessage(TextFormat::RED . "Usage: /zone <create|delete|flag|list|priority>");
            return false;
        }
        return $this->subCommands[array_shift($args)]->execute($sender, $commandLabel, $args);
    }
    
    /**
     * @param string $name
     * @param SubCommand $command
     * @param array $aliases
     */
    public function registerSubCommand(string $name, SubCommand $command, $aliases = []){
        $this->subCommands[$name] = $command;
        foreach($aliases as $alias){
            if(!isset($this->subCommands[$alias])){
                $this->registerSubCommand($alias, $command);
            }
        }
    }
    
    public function getPlugin(): Plugin{
        return LevelShield::getInstance();
    }
}
