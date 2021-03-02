<?php
namespace Gems;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

class Gem{
	
	private $shortName = "PrivateGem";
	
	private $name = "Gems";
	
	private $item;
	 
	private $tags = [];
	
	private $cooldown = 0;
	
	private $effects = [];
	
	private $playerList = [];
	
	public function __construct(string $shortName, string $name, string $it, array $tags = null, Int $cooldown, array $effects){
	    $this->shortName = $shortName."§r";
	    $this->name = "§r".$name."§r";
	    $it = explode(":", $it);
	    $id = $it[0] && is_numeric($it[0]) ? $it[0] : 0;
	    if($id == 0 && is_string($it[0])){
		   $id = constant(Item::class."::".strtoupper($it[0]));
		  }
	    $this->item = Item::get($id, $it[1] ? $it[1] : 0, 1);
	    $this->tags = $tags !== null ? $tags : [];
	    $this->cooldown = $cooldown;
	    $this->effects = $effects;
	}
	
	public function getShortName(){
	    return $this->shortName;
	}
	
	public function getName(){
	    return $this->name;
	}
		
	public function getItem(){
	    return $this->item;
	}
	
	public function getTags(){
	    return $this->tags;
	}
	
	public function getCooldown(){
	    return $this->cooldown;
	}
	
	public function getEffects(){
		 $es = [];
	    foreach($this->effects as $ed){
	      $data = explode(":", $ed);
         $ef = new EffectInstance (Effect::getEffect($data[0]));
         $ef->setAmplifier($data[1])->setDuration(20, $data[2]);
	      $es[] = $ef; 
	    }
	return $es;
	}
	
	public function checkCooldown(Player $player){
	    if(!isset($this->playerList[$player->getName()])){
		   $this->playerList[$player->getName()] = time();
		 return true;
		 }
		 if(($this->playerList[$player->getName()] + $this->getCooldown()) - time() <= 0){
			$this->playerList[$player->getName()] = time();
		   return true;
		}else{
		  return $this->playerList[$player->getName()] + $this->getCooldown() - time();
		}
	}
	
	public function isGem(Item $element){
		 $item = $this->getItem();
	return ($element->getId() == $item->getId() && $element->getDamage() == $item->getDamage());
	}
	
	public function onItemUse(Player $player, Item $item){
	    if(!$this->isGem($item)) return false;
	      if(($time = $this->checkCooldown($player)) !== true){
		     $player->sendTip("§3Cooldown required, wait §b".$time."§7 seconds");
		   return false;
		   }
	      $player->sendTip("§2aActivated effects..");
	      foreach($this->getEffects() as $effect){
	        $player->addEffect($effect);
	   }
	}
	
	public function checkName(Player $player, Item $item){
	    if($this->isGem($item)){
		   if($item->getName() !== $this->getName()){
			  $player->getInventory()->removeItem($item);
			  $item->setCustomName($this->getName());
			  $item->setLore($this->getTags());
			  $player->getInventory()->addItem($item);
			}
		}
	}
}
