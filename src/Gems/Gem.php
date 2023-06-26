<?php

namespace Gems;

use pocketmine\item\ItemFactory;

class Gem
{
    private $gemName;
    private $name;
    private $item;
    private $cooldown;
    private $uses;
    private $effects;
    
    public function __construct(string $gemName, string $name, \pocketmine\item\Item $item, int $cooldown, int $uses, array $effects)
{
    $this->gemName = $gemName;
    $this->name = $name;
    $this->item = $item;
    $this->cooldown = $cooldown;
    $this->uses = $uses;
    $this->effects = $effects;
}


    public function getGemName(): string
    {
        return $this->gemName;
    }
    public function isGemItem(\pocketmine\item\Item $item): bool
{
    $gemItems = [ // Array of known gem items
        'diamond',
        'emerald',
        // Add more gem items if needed
    ];

    // Check if the item's name or ID is in the gem items array
    $itemName = strtolower($item->getName());
    $itemId = $item->getId();

    return in_array($itemName, $gemItems) || in_array($itemId, $gemItems);
}

    public function getName(): string
    {
        return $this->name;
    }
    public function getItem(): \pocketmine\item\Item
{
    $item = clone $this->item; // Create a clone of the original item
    
    // Customize the item properties as needed
    $item->setCustomName($this->name);
    // Add any other modifications or customizations
    
    return $item;
}


    public function getCooldown(): int
    {
        return $this->cooldown;
    }

    public function getUses(): int
    {
        return $this->uses;
    }

    public function getEffects(): array
    {
        return $this->effects;
    }
}
