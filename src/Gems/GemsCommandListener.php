<?php

declare(strict_types=1);

namespace Gems;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\Player;
use pocketmine\Server;

class GemsCommandListener implements Listener
{
    /** @var GemPlugin */
    private $plugin;

    /** @var Server */
    private $server;

    public function __construct(GemPlugin $plugin, Server $server)
    {
        $this->plugin = $plugin;
        $this->server = $server;
    }

    public function onCommandPreprocess(PlayerCommandPreprocessEvent $event): void
    {
        $player = $event->getPlayer();
        $command = $event->getMessage();

        // Check if the command starts with '/'
        if (substr($command, 0, 1) === '/') {
            // Remove the leading '/'
            $command = substr($command, 1);

            // Split the command into parts
            $parts = explode(' ', $command);
            $label = array_shift($parts);
            $args = $parts;

            // Handle the gems command
            if ($label === 'gems') {
                // Check if the player has permission to use the GemsPlugin commands
                if (!$player->hasPermission('gems.command')) {
                    $player->sendMessage("You don't have permission to use this command.");
                    return;
                }

                // Remove the 'gems' label from the arguments array
                array_shift($args);

                // Create a new instance of GemsCommand and execute the command
                $gemsCommand = new GemsCommand($this->plugin, $this->server);
                $gemsCommand->execute($player, 'gems', $args);

                // Cancel the original command event
                $event->setCancelled(true);
            }
        }
    }
}
