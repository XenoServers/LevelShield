<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield;

use pocketmine\level\Level;
use pocketmine\math\Vector3;

/**
 * Class Zone
 * @package Xenophilicy\LevelShield
 */
class Zone {
    
    /** @var string */
    private $name;
    /** @var Vector3 */
    private $pos1;
    /** @var Vector3 */
    private $pos2;
    /** @var string */
    private $level;
    /** @var bool[] */
    private $flags;
    /** @var int */
    private $priority;
    
    /**
     * Zone constructor.
     * @param string $name
     * @param Vector3 $pos1
     * @param Vector3 $pos2
     * @param string $levelname
     * @param array $flags
     * @param int $priority
     */
    public function __construct(string $name, Vector3 $pos1, Vector3 $pos2, string $levelname, array $flags = [], int $priority = 0){
        $this->name = $name;
        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
        $this->level = $levelname;
        $this->flags = $flags;
        $this->priority = $priority;
        $this->save();
    }
    
    public function save(): void{
        LevelShield::getInstance()->getZoneManager()->setZone($this->name, $this);
    }
    
    public function getPos2(): Vector3{
        return $this->pos2;
    }
    
    public function getPos1(): Vector3{
        return $this->pos1;
    }
    
    public function getPriority(): int{
        return $this->priority;
    }
    
    public function setPriority(int $priority): void{
        $this->priority = $priority;
        $this->save();
    }
    
    /**
     * @return string[]
     */
    public function getFlags(): array{
        return $this->flags;
    }
    
    public function setFlags(array $flags): void{
        $this->flags = $flags;
        $this->save();
    }
    
    public function getFlag(string $flag): bool{
        if(isset($this->flags[$flag])) return $this->flags[$flag];
        return false;
    }
    
    public function setFlag(string $flag, bool $value): void{
        $this->flags[$flag] = $value;
        $this->save();
    }
    
    public function contains(Vector3 $pos, Level $level): bool{
        return ((min($this->pos1->getX(), $this->pos2->getX()) <= $pos->getX()) && (max($this->pos1->getX(), $this->pos2->getX()) >= $pos->getX()) && (min($this->pos1->getY(), $this->pos2->getY()) <= $pos->getY()) && (max($this->pos1->getY(), $this->pos2->getY()) >= $pos->getY()) && (min($this->pos1->getZ(), $this->pos2->getZ()) <= $pos->getZ()) && (max($this->pos1->getZ(), $this->pos2->getZ()) >= $pos->getZ()) && ($this->level === $level->getName()));
    }
    
    public function getLevel(): ?Level{
        LevelShield::getInstance()->getServer()->loadLevel($this->level);
        return LevelShield::getInstance()->getServer()->getLevelByName($this->level);
    }
    
    public function getName(): string{
        return $this->name;
    }
    
}
