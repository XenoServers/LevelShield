<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\command;

use pocketmine\command\CommandSender;

/**
 * Class SubCommand
 * @package Xenophilicy\LevelShield\command
 */
abstract class SubCommand {
    
    /**
     * @param CommandSender $sender
     * @param $commandLabel
     * @param array $args
     * @return mixed
     */
    abstract public function execute(CommandSender $sender, string $commandLabel, array $args);
}