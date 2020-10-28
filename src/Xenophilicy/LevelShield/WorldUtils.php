<?php
declare(strict_types=1);

namespace Xenophilicy\LevelShield;

use pocketmine\command\CommandSender;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat as TF;

/**
 * Class WorldUtils
 * @package Xenophilicy\LevelShield
 */
class WorldUtils {
    
    public static function checkData(CommandSender $sender, string $path, string $name): bool{
        if(!($levelDatContent = file_get_contents($path . DIRECTORY_SEPARATOR . "level.dat"))){
            $sender->sendMessage(TF::RED . "Target data file doesn't exist");
            return false;
        }
        $nbt = new BigEndianNBTStream();
        $levelData = $nbt->readCompressed($levelDatContent);
        if(!($levelData instanceof CompoundTag) or !$levelData->hasTag("Data", CompoundTag::class)){
            $sender->sendMessage(TF::RED . "Target data is damaged");
            return false;
        }
        $dataWorkingWith = $levelData->getCompoundTag("Data");
        if(!$dataWorkingWith->hasTag("LevelName", StringTag::class)){
            $sender->sendMessage(TF::RED . "Target data is damaged");
            return false;
        }
        $dataWorkingWith->setString("LevelName", $name);
        if(!(file_put_contents($path . DIRECTORY_SEPARATOR . "level.dat", $nbt->writeCompressed(new CompoundTag("", [$dataWorkingWith]))))){
            $sender->sendMessage(TF::RED . "Target data wasn't saved correctly");
            return false;
        }
        return true;
    }
    
    public static function delete(string $directory): void{
        if(!is_dir($directory)) return;
        $objects = scandir($directory);
        foreach($objects as $object){
            if($object === "." || $object === "..") continue;
            if(is_dir($directory . DIRECTORY_SEPARATOR . $object)){
                self::delete($directory . DIRECTORY_SEPARATOR . $object);
            }else{
                unlink($directory . DIRECTORY_SEPARATOR . $object);
            }
        }
        rmdir($directory);
    }
    
    public static function copy(string $from, string $to): void{
        if(!is_dir($from)) return;
        $objects = scandir($from);
        mkdir($to);
        foreach($objects as $object){
            if($object === "." || $object === "..") continue;
            if(is_dir($from . DIRECTORY_SEPARATOR . $object)){
                self::copy($from . DIRECTORY_SEPARATOR . $object, $to . DIRECTORY_SEPARATOR . $object);
            }else{
                copy($from . DIRECTORY_SEPARATOR . $object, $to . DIRECTORY_SEPARATOR . $object);
            }
        }
    }
}