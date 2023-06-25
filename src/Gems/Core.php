<?php
namespace Gems;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;

class Core extends PluginBase implements Listener
{
    private array $gems = [];

    public function onLoad(): void
    {
        $this->saveResource("config.yml");
    }

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->loadGems();
    }

    public function onDisable(): void
    {
        // Plugin shutdown logic
    }

    public function loadGems(): void
    {
        $configData = $this->getConfig()->getAll();
        foreach ($configData as $shortName => $data) {
            if (!isset($data["name"]) || !isset($data["item"]) || !isset($data["cooldown"]) || !isset($data["effects"])) {
                $this->getLogger()->warning("Invalid gem data for: " . $shortName);
                continue;
            }

            $gem = new Gem(
                $shortName,
                $data["name"],
                $data["item"],
                $data["cooldown"],
                $data["effects"],
                $data["tags"] ?? []
            );
            $this->gems[$shortName] = $gem;
            $this->getLogger()->info("Loaded: " . $shortName);
        }
    }

    /**
     * @param PlayerItemHeldEvent $event
     */
    public function onHeld(PlayerItemHeldEvent $event): void
    {
        $player = $event->getPlayer();
        $inventory = $player->getInventory();

        if ($inventory === null) {
            return;
        }

        foreach ($inventory->getContents() as $slot => $content) {
            foreach ($this->gems as $name => $gem) {
                $gem->checkName($player, $content, $slot);
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onUse(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        foreach ($this->gems as $name => $gem) {
            if ($gem->isGem($item)) {
                $event->setCancelled();

                if ($gem->checkCooldown($player)) {
                    $gem->onItemUse($event);
                } else {
                    $cooldown = $gem->getRemainingCooldown($player);
                    $player->sendMessage("§3Cooldown required, wait §b" . $cooldown . "§7 seconds");
                }

                break;
            }
        }
    }
}

