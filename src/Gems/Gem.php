<?php

namespace Gems;

use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;
use pocketmine\Server;
use pocketmine\plugin\PluginLoader;
use pocketmine\plugin\ResourceProvider;

class Gem extends PluginBase implements Listener
{
    private string $shortName;
    private string $name;
    private string $item;
    private int $cooldown;
    private array $effects;
    private array $tags;

    public function onLoad(): void
    {
        $this->saveResource("config.yml");
    }

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable(): void
    {
        // Plugin shutdown logic
    }

    public function __construct(
        PluginLoader $loader,
        Server $server,
        PluginDescription $description,
        string $dataFolder,
        string $file,
        ResourceProvider $resourceProvider
    ) {
        parent::__construct($loader, $server, $description, $dataFolder, $file, $resourceProvider);
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function getGemName(): string
    {
        return $this->name;
    }
    public function getItem(): Item
    {
        return $this->item;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getCooldown(): int
    {
        return $this->cooldown;
    }

    public function getEffects(): array
    {
        $effectInstances = [];
        foreach ($this->effects as $effectData) {
            $data = explode(":", $effectData);
            $effect = new EffectInstance(Effect::getEffect((int)$data[0]));
            $effect->setAmplifier((int)$data[1])->setDuration(20 * (int)$data[2]);
            $effectInstances[] = $effect;
        }
        return $effectInstances;
    }

    public function checkCooldown(Player $player): bool
    {
        $this->playerList[$player->getName()] = time();
        return true;
    }

    public function isGem(Item $element): bool
    {
        $item = $this->getItem();
        return $element->getId() === $item->getId() && $element->getDamage() === $item->getDamage();
    }

    public function onItemUse(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if (!$this->isGem($item)) {
            return;
        }

        if ($this->checkCooldown($player)) {
            $player->sendTip("§2Activated effects..");
            foreach ($this->getEffects() as $effect) {
                $player->addEffect($effect);
            }
        } else {
            $cooldown = $this->getRemainingCooldown($player);
            $player->sendTip("§3Cooldown required, wait §b" . $cooldown . "§7 seconds");
        }
    }

    public function checkName(Player $player, Item $element, int $slot): void
    {
        if ($this->isGem($element)) {
            $player->sendPopup("§6You are holding a §f" . $this->getName() . "§6!");
        }
    }

    public function getRemainingCooldown(Player $player): int
    {
        $lastUsage = $this->playerList[$player->getName()] ?? 0;
        $remainingCooldown = $this->getCooldown() - (time() - $lastUsage);
        return max(0, $remainingCooldown);
    }

    private function parseItemString(string $itemString): Item
    {
        $parts = explode(":", $itemString);
        $itemId = (int)$parts[0];
        $itemDamage = isset($parts[1]) ? (int)$parts[1] : 0;
        return Item::get($itemId, $itemDamage);
    }
}
