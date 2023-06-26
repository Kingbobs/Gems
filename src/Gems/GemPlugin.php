<?php

declare(strict_types=1);

namespace Gems;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

class GemPlugin extends PluginBase implements Listener {

    /** @var Gem[] */
    private $gems = [];

    /** @var Config */
    private $config;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->loadConfig();
        $this->loadGems();

        // Register the GemsCommand
        $this->getServer()->getCommandMap()->register("gems", new GemsCommand($this));
    }

    private function loadConfig(): void {
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }
    private function loadGems(): void
{
    $gems = $this->config->get("gems", []);

    foreach ($gems as $gemName => $gemData) {
        $itemData = is_string($gemData["item"]) ? explode(":", $gemData["item"]) : [];
        $itemId = isset($itemData[0]) ? intval($itemData[0]) : 0;
        $itemMeta = isset($itemData[1]) ? intval($itemData[1]) : 0;
        $gemItem = ItemFactory::getInstance()->get($itemId, $itemMeta);

        $cooldown = intval($gemData["cooldown"]);
        $uses = intval($gemData["uses"]);
        $effects = [];

        foreach ($gemData["effects"] as $effectData) {
            $effectId = $effectData["id"];
            $effectAmplifier = intval($effectData["amplifier"]);
            $effectDuration = intval($effectData["duration"]);
            $effects[] = [
                "id" => $effectId,
                "amplifier" => $effectAmplifier,
                "duration" => $effectDuration
            ];
        }

        $gem = new Gem($gemName, $gemData["name"], $gemItem, $cooldown, $uses, $effects);
        $this->gems[$gemName] = $gem;
    }
}
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
{
    if ($command->getName() === "gem") {
        if (empty($args[0])) {
            $sender->sendMessage("Usage: /gem <gemName>");
            return true;
        }

        $gemName = $args[0];

        if (isset($this->gems[$gemName])) {
            $gem = $this->gems[$gemName];
            if ($gem->getUses() > 0) {
                $player = $sender instanceof Player ? $sender : null; // Set the player to null if command sender is not a player

                // Customize the gem item as needed
                $gemItem = $gem->getItem();
                $gemItem->setCustomName($gemName);
                $gemItem->setNamedTagEntry(new StringTag("gemName", $gemName)); // Add gem name as a tag

                // Give the gem item to the player or console sender
                if ($player !== null) {
                    $player->getInventory()->addItem($gemItem);
                    $player->sendMessage("You received a gem: " . $gemName);
                } else {
                    // Apply effects directly to the console sender
                    $this->applyEffectsToSender($sender, $gem->getEffects());
                    $sender->sendMessage("You received the effects from the gem: " . $gemName);
                }

                $gem->decrementUses();
            } else {
                $sender->sendMessage("Gem out of uses.");
            }
        } else {
            $sender->sendMessage("Invalid gem name.");
        }

        return true;
    }

    return false;
}

private function applyEffectsToSender(CommandSender $sender, array $effects): void
{
    foreach ($effects as $effectData) {
        $effectId = $effectData["id"];
        $effectDuration = $effectData["duration"];
        $effectAmplifier = $effectData["amplifier"];

        $effect = Gem::getEffect($effectId);
        if ($effect !== null) {
            $effect->setDuration($effectDuration);
            $effect->setAmplifier($effectAmplifier);

            $sender->addEffect(new EffectInstance($effect));
        }
    }
}
    /**
     * @param PlayerInteractEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
public function onPlayerInteract(PlayerInteractEvent $event): void {
    $player = $event->getPlayer();
    $item = $event->getItem();

    foreach ($this->gems as $gemName => $gemData) {
        $gemItem = $gemData->getItem();

        if ($item->equals($gemItem)) {
            // Execute the command to give the boost (effect) to the player
            $command = "effect {$player->getName()} minecraft:haste {$gemData->getCooldown()} {$gemData->getUses()}";
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);

            // Handle custom gem item usage here
            $player->sendMessage("You used a gem!");

            // Cancel the event to prevent default interaction
            $event->setCancelled();
            break;
        }
    }
}



    public function getEffect(string $effectName): ?Effect {
        return Effect::getEffectByName($effectName);
    }
}
