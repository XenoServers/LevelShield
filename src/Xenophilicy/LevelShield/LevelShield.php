<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield;

use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\LevelShield\command\BaseWorld;
use Xenophilicy\LevelShield\command\BaseZone;
use Xenophilicy\LevelShield\command\CopyWorld;
use Xenophilicy\LevelShield\command\CreateWorld;
use Xenophilicy\LevelShield\command\CreateZone;
use Xenophilicy\LevelShield\command\DeleteWorld;
use Xenophilicy\LevelShield\command\DeleteZone;
use Xenophilicy\LevelShield\command\FlagWorld;
use Xenophilicy\LevelShield\command\FlagZone;
use Xenophilicy\LevelShield\command\ListWorld;
use Xenophilicy\LevelShield\command\ListZone;
use Xenophilicy\LevelShield\command\LoadWorld;
use Xenophilicy\LevelShield\command\PriorityZone;
use Xenophilicy\LevelShield\command\RenameWorld;
use Xenophilicy\LevelShield\command\TeleportWorld;
use Xenophilicy\LevelShield\command\UnloadWorld;
use Xenophilicy\LevelShield\libs\jojoe77777\FormAPI\CustomForm;
use Xenophilicy\LevelShield\manager\WorldManager;
use Xenophilicy\LevelShield\manager\ZoneManager;

/**
 * Class LevelShield
 * @package Xenophilicy\LevelShield
 */
class LevelShield extends PluginBase implements Listener {
    
    /** @var array */
    public static $formCache = [];
    /** @var array */
    public static $creation = [];
    /** @var array */
    private static $settings;
    /** @var LevelShield */
    private static $instance;
    /** @var ZoneManager */
    private $zonemanager;
    /** @var WorldManager */
    private $worldmanager;
    
    public static function getDefaultFlags(): array{
        return self::$settings["default"];
    }
    
    public static function getFallback(string $flag): bool{
        return self::$settings["fallback"][$flag];
    }
    
    public static function getMessage(string $setting): ?string{
        return self::$settings["messages"][$setting] ?? null;
    }
    
    public static function getInstance(): self{
        return self::$instance;
    }
    
    public function onEnable(): void{
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->saveDefaultConfig();
        self::$settings = $this->getConfig()->getAll();
        $this->zonemanager = new ZoneManager();
        $this->worldmanager = new WorldManager();
        $this->saveResource("zones.json");
        $this->saveResource("worlds.json");
        $zones = new Config($this->getDataFolder() . "zones.json", Config::JSON);
        $worlds = new Config($this->getDataFolder() . "worlds.json", Config::JSON);
        $directory = $this->getWorldFolderPath("");
        if(!is_dir($directory)) return;
        $objects = scandir($directory);
        foreach($objects as $object){
            if($object === "." || $object === "..") continue;
            $world = $this->getWorldManager()->getWorld($object);
            if(!is_null($world)) continue;
            $this->getWorldManager()->registerWorld($object);
        }
        $this->getWorldManager()->saveWorlds();
        foreach($worlds->getAll() as $name => $flags){
            if(!$this->getServer()->isLevelGenerated($name)){
                $this->getLogger()->critical("World {$name} doesn't exist, it will be disabled");
                continue;
            }
            $this->getWorldManager()->addWorld($name, $flags);
        }
        foreach($zones->getAll() as $name => $zone){
            $pos1 = new Vector3($zone["pos1"][0], $zone["pos1"][1], $zone["pos1"][2]);
            $pos2 = new Vector3($zone["pos2"][0], $zone["pos2"][1], $zone["pos2"][2]);
            if(!$this->getServer()->isLevelGenerated($zone["level"])){
                $this->getLogger()->critical("Zone {$name} is attached to a level that doesn't exist, it will be disabled");
                continue;
            }
            $this->getZoneManager()->addZone($name, $pos1, $pos2, $zone["level"], $zone["flags"], $zone["priority"]);
        }
        $base = new BaseZone();
        $this->getServer()->getCommandMap()->register("zone", $base);
        $base->registerSubCommand("list", new ListZone(), ["all", "show"]);
        $base->registerSubCommand("create", new CreateZone(), ["new", "set", "add"]);
        $base->registerSubCommand("priority", new PriorityZone(), ["pri", "order"]);
        $base->registerSubCommand("flag", new FlagZone(), ["flg"]);
        $base->registerSubCommand("delete", new DeleteZone(), ["remove", "del", "rem"]);
        $base = new BaseWorld();
        $this->getServer()->getCommandMap()->register("world", $base);
        $base->registerSubCommand("flag", new FlagWorld(), ["flg"]);
        $base->registerSubCommand("list", new ListWorld(), ["all", "show"]);
        $base->registerSubCommand("create", new CreateWorld(), ["new", "set", "add"]);
        $base->registerSubCommand("delete", new DeleteWorld(), ["remove", "del", "rem"]);
        $base->registerSubCommand("load", new LoadWorld());
        $base->registerSubCommand("unload", new UnloadWorld());
        $base->registerSubCommand("copy", new CopyWorld());
        $base->registerSubCommand("rename", new RenameWorld());
        $base->registerSubCommand("teleport", new TeleportWorld(), ["tp"]);
    }
    
    public function getWorldFolderPath(string $name): string{
        return getcwd() . DIRECTORY_SEPARATOR . "worlds" . DIRECTORY_SEPARATOR . $name;
    }
    
    public function getWorldManager(): WorldManager{
        return $this->worldmanager;
    }
    
    public function getZoneManager(): ZoneManager{
        return $this->zonemanager;
    }
    
    public function onDisable(){
        $this->getZoneManager()->saveZones();
        $this->getWorldManager()->saveWorlds();
    }
    
    public function flagForm(Player $player): void{
        $form = new CustomForm(function(Player $player, $data){
            if($data === null){
                return;
            }
            $cache = self::$formCache[$player->getName()];
            if($cache["type"] === "world"){
                $object = $this->getWorldManager()->getWorld($cache["name"]);
            }else{
                $object = $this->getZoneManager()->getZone($cache["name"]);
                $object->setPriority(intval($data["pri"]));
            }
            $object->setFlag("interact", $data["int"]);
            $object->setFlag("damage", $data["dmg"]);
            $object->setFlag("pvp", $data["pvp"]);
            $object->setFlag("edit", $data["edt"]);
            $object->setFlag("fly", $data["fly"]);
            $object->setFlag("drop", $data["drp"]);
            $object->setFlag("hunger", $data["hgr"]);
            $object->setFlag("explode", $data["exp"]);
            $object->setFlag("bow", $data["bow"]);
            $object->setFlag("burn", $data["brn"]);
            $object->setFlag("effects", $data["eff"]);
            $player->sendMessage(TF::GREEN . "Flags set");
        });
        $cache = self::$formCache[$player->getName()];
        $form->setTitle(TF::DARK_AQUA . TF::BOLD . ucfirst($cache["type"]) . " | " . ucfirst($cache["name"]));
        if($cache["type"] === "world"){
            $object = $this->getWorldManager()->getWorld($cache["name"]);
        }else{
            $object = $this->getZoneManager()->getZone($cache["name"]);
            $form->addLabel("Set the zone priority level");
            $form->addInput("Priority", "0", (string)$object->getPriority(), "pri");
        }
        $form->addLabel("Allow player interactions");
        $form->addToggle("Interact", $object->getFlag("interact"), "int");
        $form->addLabel("Allow players to take damage");
        $form->addToggle("Damage", $object->getFlag("damage"), "dmg");
        $form->addLabel("Allow players to PvP");
        $form->addToggle("PvP", $object->getFlag("pvp"), "pvp");
        $form->addLabel("Allow players to break and place blocks");
        $form->addToggle("Edit", $object->getFlag("edit"), "edt");
        $form->addLabel("Allow players to fly");
        $form->addToggle("Fly", $object->getFlag("fly"), "fly");
        $form->addLabel("Allow players to drop items");
        $form->addToggle("Drop", $object->getFlag("drop"), "drp");
        $form->addLabel("Enable hunger loss");
        $form->addToggle("Hunger", $object->getFlag("hunger"), "hgr");
        $form->addLabel("Enable explosions");
        $form->addToggle("Explode", $object->getFlag("explode"), "exp");
        $form->addLabel("Allow players to use bows");
        $form->addToggle("Bow", $object->getFlag("bow"), "bow");
        $form->addLabel("Allow blocks to burn and be lit on fire");
        $form->addToggle("Burn", $object->getFlag("burn"), "brn");
        $form->addLabel("Allow players to have effects");
        $form->addToggle("Effects", $object->getFlag("effects"), "eff");
        $player->sendForm($form);
    }
}
