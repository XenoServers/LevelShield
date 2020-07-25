<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\manager;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\level\Position;
use pocketmine\Player;
use Xenophilicy\LevelShield\LevelShield;

/**
 * Class PermissionManager
 * @package Xenophilicy\LevelShield
 */
class PermissionManager {
    
    public static function canHaveEffect(Entity $entity): bool{
        if($entity instanceof Player && $entity->hasPermission("levelshield.override")) return true;
        return self::getHighestPriority("effects", $entity);
    }
    
    private static function getHighestPriority(string $flag, Position $pos): bool{
        $level = $pos->getLevel();
        $priority = -PHP_INT_MAX;
        $finalFlag = LevelShield::getInstance()->getWorldManager()->getWorld($level->getName())->getFlag($flag) ?? LevelShield::getFallback($flag);
        foreach(LevelShield::getInstance()->getZoneManager()->getZones() as $zone){
            if(!$zone->contains($pos, $level)) continue;
            if($zone->getPriority() < $priority) continue;
            $priority = $zone->getPriority();
            $finalFlag = $zone->getFlag($flag);
        }
        return $finalFlag;
    }
    
    public static function canBurn(Block $block): bool{
        return self::getHighestPriority("burn", $block);
    }
    
    public static function canShootBow(Living $entity): bool{
        if($entity instanceof Player && $entity->hasPermission("levelshield.override")) return true;
        return self::getHighestPriority("bow", $entity);
    }
    
    public static function canExplode(Position $position): bool{
        return self::getHighestPriority("explode", $position);
    }
    
    public static function canExhaust(Human $player): bool{
        if($player instanceof Player && $player->hasPermission("levelshield.override")) return true;
        return self::getHighestPriority("hunger", $player);
    }
    
    public static function canDrop(Player $player): bool{
        if($player->hasPermission("levelshield.override")) return true;
        return self::getHighestPriority("drop", $player);
    }
    
    public static function canInteract(Player $player, Position $position): bool{
        if($player->hasPermission("levelshield.override")) return true;
        return self::getHighestPriority("interact", $position);
    }
    
    public static function canEdit(Player $player, Position $position): bool{
        if($player->hasPermission("levelshield.override")) return true;
        return self::getHighestPriority("edit", $position);
    }
    
    public static function canBeDamaged(Player $player): bool{
        return self::getHighestPriority("damage", $player);
    }
    
    public static function canFly(Player $player): bool{
        if($player->hasPermission("levelshield.override")) return true;
        return self::getHighestPriority("fly", $player);
    }
    
    public static function canPvP(Player $victim, Player $damager): bool{
        if($damager->hasPermission("levelshield.override")) return true;
        $level = $victim->getLevel();
        $damagerZone = null;
        $victimZone = null;
        $finalzone = LevelShield::getInstance()->getWorldManager()->getWorld($level->getName())->getFlag("pvp") ?? LevelShield::getFallback("pvp");
        $priority = -PHP_INT_MAX;
        foreach(LevelShield::getInstance()->getZoneManager()->getZones() as $zone){
            if(!$zone->contains($victim, $level)) continue;
            if($zone->getPriority() < $priority) continue;
            $priority = $zone->getPriority();
            $victimZone = $zone->getFlag("pvp");
        }
        $priority = -PHP_INT_MAX;
        foreach(LevelShield::getInstance()->getZoneManager()->getZones() as $zone){
            if(!$zone->contains($damager, $level)) continue;
            if($zone->getPriority() < $priority) continue;
            $priority = $zone->getPriority();
            $damagerZone = $zone->getFlag("pvp");
        }
        if(is_null($damagerZone) && is_null($victimZone)) return $finalzone;
        if(!$damagerZone || !$victimZone) return false;
        if(is_null($damagerZone)){
            return $victimZone;
        }else{
            return $damagerZone;
        }
    }
}