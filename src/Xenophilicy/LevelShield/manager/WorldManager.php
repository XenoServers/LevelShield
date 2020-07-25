<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\manager;

use Xenophilicy\LevelShield\LevelShield;
use Xenophilicy\LevelShield\World;

/**
 * Class WorldManager
 * @package Xenophilicy\LevelShield
 */
class WorldManager {
    
    /** @var World[] */
    private static $worlds = [];
    
    public function construct(){
        self::$worlds = [];
    }
    
    public function saveWorlds(): void{
        $worlds = [];
        foreach(self::$worlds as $world){
            $worlds[$world->getName()] = $world->getFlags();
        }
        file_put_contents(LevelShield::getInstance()->getDataFolder() . "worlds.json", json_encode($worlds));
    }
    
    public function setWorld(string $name, World $world): void{
        self::$worlds[$name] = $world;
    }
    
    public function addWorld(string $name, array $flags): void{
        self::$worlds[$name] = new World($name, $flags);
    }
    
    public function registerWorld(string $name): World{
        $world = new World($name);
        foreach(LevelShield::getDefaultFlags() as $flag => $value){
            $world->setFlag($flag, $value);
        }
        self::$worlds[$name] = new World($name, $world->getFlags());
        return $world;
    }
    
    public function deleteWorld(string $name): void{
        $zones = LevelShield::getInstance()->getZoneManager()->getZones();
        foreach($zones as $zone){
            if($zone->getLevel()->getFolderName() === $name) LevelShield::getInstance()->getZoneManager()->deleteZone($zone->getName());
        }
        unset(self::$worlds[$name]);
    }
    
    public function getWorld(string $name): ?World{
        return self::$worlds[$name] ?? null;
    }
    
    /**
     * @return World[]
     */
    public function getWorlds(): array{
        return self::$worlds;
    }
}