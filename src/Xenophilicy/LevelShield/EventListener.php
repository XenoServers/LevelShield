<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\FlintSteel;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\manager\PermissionManager;

/**
 * Class EventListener
 * @package Xenophilicy\LevelShield
 */
class EventListener implements Listener {
    
    private static $lastTap;
    
    public function onLevelLoad(LevelLoadEvent $event): void{
        $level = $event->getLevel()->getName();
        $world = LevelShield::getInstance()->getWorldManager()->getWorld($level);
        if(!is_null($world)) return;
        LevelShield::getInstance()->getWorldManager()->registerWorld($level);
    }
    
    /**
     * @ignoreCancelled true
     * @param PlayerInteractEvent $event
     */
    public function onInteract(PlayerInteractEvent $event): void{
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $item = $event->getItem();
        if(isset(LevelShield::$creation[$player->getName()])){
            $event->setCancelled();
            $data = LevelShield::$creation[$player->getName()];
            $pos = new Vector3($block->getFloorX(), $block->getFloorY(), $block->getFloorZ());
            $posString = "{$pos->x}, {$pos->y}, {$pos->z}";
            if(isset(self::$lastTap[$player->getName()]) && self::$lastTap[$player->getName()] + 1 > time()) return;
            if($data[1] === false){
                LevelShield::$creation[$player->getName()] = [$data[0], $pos, false];
                $player->sendMessage(TF::GREEN . "Position 1 set to " . TF::AQUA . $posString);
                $player->sendMessage(TF::YELLOW . "Tap a block to set position 2");
                self::$lastTap[$player->getName()] = time();
            }else{
                $data = [$data[0], $data[1], $pos];
                $player->sendMessage(TF::GREEN . "Position 2 set to " . TF::AQUA . $posString);
                $zone = LevelShield::getInstance()->getZoneManager()->createZone($data[0], $data[1], $data[2], $player);
                $player->sendMessage(TF::GREEN . "Zone created with name " . TF::AQUA . $zone->getName());
                unset(LevelShield::$creation[$player->getName()]);
            }
            return;
        }
        if($item instanceof FlintSteel && !$player->hasPermission("levelshield.override") && !PermissionManager::canBurn($block)) $event->setCancelled();
        if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK && PermissionManager::canEdit($player, $block)) return;
        if(!PermissionManager::canInteract($player, $block)) $event->setCancelled();
    }
    
    public function onEffect(EntityEffectAddEvent $event): void{
        if(!PermissionManager::canHaveEffect($event->getEntity())) $event->setCancelled();
    }
    
    /**
     * @ignoreCancelled true
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event): void{
        if(!PermissionManager::canEdit($event->getPlayer(), $event->getBlock())){
            $event->setCancelled();
            return;
        }
        $event->setCancelled(false);
    }
    
    public function onBlockBreak(BlockBreakEvent $event): void{
        if(!PermissionManager::canEdit($event->getPlayer(), $event->getBlock())) $event->setCancelled();
    }
    
    public function onEntityDamage(EntityDamageEvent $event): void{
        $entity = $event->getEntity();
        if(!$entity instanceof Player) return;
        if($event->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK) return;
        if(!PermissionManager::canBeDamaged($entity)) $event->setCancelled();
    }
    
    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void{
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        if(!$entity instanceof Player || !$damager instanceof Player) return;
        if(!PermissionManager::canPvP($entity, $damager)) $event->setCancelled();
    }
    
    public function onPlayerMove(PlayerMoveEvent $event): void{
        $player = $event->getPlayer();
        if($player->isFlying() && !PermissionManager::canFly($player) && !$player->isCreative() && !$player->isSpectator()){
            $player->setFlying(false);
            $player->setAllowFlight(false);
            $player->sendMessage(LevelShield::getMessage("no-fly"));
        }
        if($player->hasEffects() && !PermissionManager::canHaveEffect($player)) $player->removeAllEffects();
    }
    
    public function onItemDrop(PlayerDropItemEvent $event): void{
        if(!PermissionManager::canDrop($event->getPlayer())) $event->setCancelled();
    }
    
    public function onExhaust(PlayerExhaustEvent $event): void{
        if(!PermissionManager::canExhaust($event->getPlayer())) $event->setCancelled();
    }
    
    public function onExplode(EntityExplodeEvent $event): void{
        if(!PermissionManager::canExplode($event->getPosition())){
            $event->setCancelled();
            return;
        }
        $blocks = [];
        foreach($event->getBlockList() as $block){
            if(!PermissionManager::canExplode($block)) array_push($blocks, $block);
        }
        var_dump($blocks);
        $event->setBlockList(array_diff($event->getBlockList(), $blocks));
    }
    
    public function onBowShoot(EntityShootBowEvent $event): void{
        if(!PermissionManager::canShootBow($event->getEntity())) $event->setCancelled();
    }
    
    public function onBurn(BlockBurnEvent $event): void{
        if(!PermissionManager::canBurn($event->getBlock())) $event->setCancelled();
    }
}