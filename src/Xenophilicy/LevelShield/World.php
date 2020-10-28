<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield;

use pocketmine\level\Level;

/**
 * Class World
 * @package Xenophilicy\LevelShield
 */
class World {
    
    /** @var string */
    private $name;
    /** @var bool[] */
    private $flags;
    
    /**
     * World constructor.
     * @param string $name
     * @param array $flags
     */
    public function __construct(string $name, array $flags = []){
        $this->name = $name;
        $this->flags = $flags;
        $this->save();
    }
    
    public function save(): void{
        LevelShield::getInstance()->getWorldManager()->setWorld($this->name, $this);
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
    
    public function getLevel(): ?Level{
        return LevelShield::getInstance()->getServer()->getLevelByName($this->name);
    }
    
    public function getName(): string{
        return $this->name;
    }
    
}
