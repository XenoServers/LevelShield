<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield\manager;

use pocketmine\math\Vector3;
use pocketmine\Player;
use Xenophilicy\LevelShield\LevelShield;
use Xenophilicy\LevelShield\Zone;

/**
 * Class ZoneManager
 * @package Xenophilicy\LevelShield
 */
class ZoneManager {
    
    /** @var Zone[] */
    private static $zones = [];
    
    public function construct(){
        self::$zones = [];
    }
    
    public function saveZones(): void{
        $zones = [];
        foreach(self::$zones as $zone){
            $zones[$zone->getName()] = ["flags" => $zone->getFlags(), "pos1" => [$zone->getPos1()->getFloorX(), $zone->getPos1()->getFloorY(), $zone->getPos1()->getFloorZ()], "pos2" => [$zone->getPos2()->getFloorX(), $zone->getPos2()->getFloorY(), $zone->getPos2()->getFloorZ()], "level" => $zone->getLevel()->getName(), "priority" => $zone->getPriority()];
        }
        file_put_contents(LevelShield::getInstance()->getDataFolder() . "zones.json", json_encode($zones));
    }
    
    public function addZone(string $name, Vector3 $pos1, Vector3 $pos2, string $level, array $flags, int $priority): void{
        self::$zones[$name] = new Zone($name, $pos1, $pos2, $level, $flags, $priority);
    }
    
    public function setZone(string $name, Zone $zone): void{
        self::$zones[$name] = $zone;
    }
    
    public function createZone(string $name, Vector3 $pos1, Vector3 $pos2, Player $player): Zone{
        $zone = new Zone($name, $pos1, $pos2, $player->getLevel()->getName());
        foreach(LevelShield::getDefaultFlags() as $flag => $value){
            $zone->setFlag($flag, $value);
        }
        self::$zones[$name] = $zone;
        return $zone;
    }
    
    public function deleteZone(string $name): void{
        unset(self::$zones[$name]);
    }
    
    public function getZone(string $name): ?Zone{
        return self::$zones[$name] ?? null;
    }
    
    /**
     * @return Zone[]
     */
    public function getZones(): array{
        return self::$zones;
    }
}