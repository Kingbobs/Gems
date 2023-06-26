<?php

declare(strict_types=1);

namespace Gems;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class GemsCommand extends Command
{
    /** @var GemPlugin */
    private $plugin;

    /** @var Server */
    private $server;

    public function __construct(GemPlugin $plugin)
    {
        parent::__construct("gems", "Manage gems", "/gems <subcommand> [args]");
        $this->plugin = $plugin;
        $this->server = $plugin->getServer();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (empty($args)) {
            $sender->sendMessage("Usage: /gems <subcommand> [args]");
            return false;
        }

        $subCommand = strtolower(array_shift($args));
        switch ($subCommand) {
            case "list":
                $this->listGems($sender);
                break;
            case "reload":
                $this->reloadConfig($sender);
                break;
            case "give":
                $this->giveGem($sender, $args);
                break;
            default:
                $sender->sendMessage("Unknown subcommand: {$subCommand}");
                break;
        }

        return true;
    }

    private function listGems(CommandSender $sender): void
    {
        $gems = $this->plugin->getConfig()->get("gems", []);
        $sender->sendMessage(TextFormat::YELLOW . "List of Gems:");
        foreach ($gems as $gemName => $gemData) {
            $sender->sendMessage(TextFormat::AQUA . "- {$gemName}");
        }
    }

    private function reloadConfig(CommandSender $sender): void
    {
        $this->plugin->reloadConfig();
        $sender->sendMessage("Configuration reloaded.");
    }

    private function giveGem(CommandSender $sender, array $args): void
    {
        if (count($args) < 2) {
            $sender->sendMessage("Usage: /gems give <player> <gem>");
            return;
        }

        $playerName = array_shift($args);
        $gemName = strtolower(array_shift($args));

        $player = $this->server->getPlayerExact($playerName);
        if (!$player instanceof Player) {
            $sender->sendMessage("Player not found: {$playerName}");
            return;
        }

        $gems = $this->plugin->getConfig()->get("gems", []);
        if (!isset($gems[$gemName])) {
            $sender->sendMessage("Gem not found: {$gemName}");
            return;
        }

        $gemData = $gems[$gemName];
        $item = ItemFactory::getInstance()->get($gemData["item"]["id"], $gemData["item"]["meta"]);
        $player->getInventory()->addItem($item);

        $sender->sendMessage("Gem given to player: {$playerName}");
    }
}
