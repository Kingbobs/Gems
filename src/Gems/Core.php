<?php
namespace Gems;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;

class Core extends PluginBase implements Listener{
	
	private static $gems = [];
	
	public function onLoad(){
		 $this->getLogger()->info("§aLoading gems...");
	    $this->saveResource("config.yml", false);
	    $this->loadGems();
	}
	
	public function onEnable(){
	    $this->getServer()->getPluginManager()->registerEvents($this, $this);
       $this->getLogger()->info("§eGems §7has been loaded successfully!");
	}
	
	public function loadGems(){
	    foreach($this->getConfig()->getAll() as $shortName => $data){
	       self::$gems[$shortName] = new Gem($shortName, $data["name"], $data["item"], isset($data["tags"]) ? $data["tags"] : [], $data["cooldown"], $data["effects"]);
	       $this->getLogger()->info("§7Loaded §e".$shortName);
	   }
	}
	
	public function onHeld(PlayerItemHeldEvent $event){
        foreach($event->getPlayer()->getInventory()->getContents() as $content){
	       foreach(self::$gems as $name => $gem){
	         $gem->checkName($event->getPlayer(), $content);
	      }
      }
	}
	
	public function onUse(PlayerInteractEvent $event){
	    foreach(self::$gems as $name => $gem){
	       $gem->onItemUse($event->getPlayer(), $event->getItem());
	       if($gem->isGem($event->getItem())) $event->setCancelled();
	    }
	}
}