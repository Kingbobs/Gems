<?php

namespace Gems;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

class Gem {

	private $shortName = "PrivateGem";
	private $name = "Gems";
	private $item;
	private $tags = [];
	private $cooldown = 0;
	private $effects = [];
	private $playerList = [];

	public function __construct(string $shortName, string $name, string $item, array $tags = [], int $cooldown, array $effects) {
		$this->shortName = $shortName;
		$this->name = $name;
		$this->item = $this->parseItemString($item);
		$this->tags = $tags;
		$this->cooldown = $cooldown;
		$this->effects = $effects;
	}

	public function getShortName(): string {
		return $this->shortName;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getItem(): Item {
		return $this->item;
	}

	public function getTags(): array {
		return $this->tags;
	}

	public function getCooldown(): int {
		return $this->cooldown;
	}

	public function getEffects(): array {
		$effectInstances = [];
		foreach ($this->effects as $effectData) {
			$data = explode(":", $effectData);
			$effect = new EffectInstance(Effect::getEffect($data[0]));
			$effect->setAmplifier($data[1])->setDuration(20 * $data[2]);
			$effectInstances[] = $effect;
		}
		return $effectInstances;
	}

	public function checkCooldown(Player $player) {
		if (!isset($this->playerList[$player->getName()])) {
			$this->playerList[$player->getName()] = time();
			return true;
		}
		$remainingCooldown = ($this->playerList[$player->getName()] + $this->getCooldown()) - time();
		if ($remainingCooldown <= 0) {
			$this->playerList[$player->getName()] = time();
			return true;
		} else {
			return $remainingCooldown;
		}
	}

	public function isGem(Item $element): bool {
		$item = $this->getItem();
		return ($element->getId() == $item->getId() && $element->getDamage() == $item->getDamage());
	}

	public function onItemUse(Player $player, Item $item) {
		if (!$this->isGem($item)) {
			return false;
		}
		if (($time = $this->checkCooldown($player)) !== true) {
			$player->sendTip("§3Cooldown required, wait §b".$time."§7 seconds");
			return false;
		}
		$player->sendTip("§2Activated effects..");
		foreach ($this->getEffects() as $effect) {
			$player->addEffect($effect);
		}
	}

	public function checkName(Player $player, Item $item) {
		if ($this->isGem($item)) {
			if ($item->getName() !== $this->getName()) {
				$player->getInventory()->removeItem($item);
				$item->setCustomName($this->getName());
				$item->setLore($this->getTags());
				$player->getInventory()->addItem($item);
			}
		}
	}

	private function parseItemString(string $itemString): Item {
		$itemData = explode(":", $itemString);
		$id = $itemData[0] && is_numeric($itemData[0]) ? $itemData[0] : 0;
		if ($id == 0 && is_string($itemData[0])) {
			$id = constant(Item::class."::".strtoupper($itemData[0]));
		}
		return Item::get($id, $itemData[1] ?? 0, 1);
	}
}
