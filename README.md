# Gems
The Gems plugin is a powerful tool designed for PocketMine-MP Minecraft servers. It allows server owners to introduce special gems into their server, providing players with unique abilities and effects to enhance their gameplay experience.

# Features
- Define custom gems with customizable properties such as name, appearance, cooldown, and effects.
- Each gem can be associated with specific item types, making it easy for players to identify and use them.
- Prevents gem item abuse by enforcing cooldown restrictions.
- Activate custom effects and abilities when players interact with gem items.
- Automatic name and lore correction to maintain consistency across the server.

# Installation
Download the latest plugin release from the GitHub repository.
Place the downloaded plugin file in the plugins folder of your PocketMine-MP server.
Start or restart your server to enable the Gems plugin.
Configuration
The Gems plugin uses a configuration file (config.yml) to define the properties of each gem. Modify this file to add, remove, or customize gems according to your preferences. Each gem entry includes the following properties:

- name: The name of the gem.
- item: The item associated with the gem in the format item_id:meta (e.g., 276:0 for a diamond sword).
- tags (optional): An array of tags or lore lines for the gem item.
- cooldown: The cooldown period in seconds before the gem can be used again.
- effects: An array of effects to be applied when the gem is used, in the format effect_id:amplifier:duration (e.g., 1:1:600 for speed I for 30 seconds).

# Usage
Obtain the gem items defined in the configuration either through creative mode or other means.
Hold the gem item in hand and right-click to activate its effects.
If the gem is on cooldown, a message will be displayed indicating the remaining cooldown time.
Enjoy the special abilities and effects granted by the activated gem.

# Contributing
Contributions to the Gems plugin are welcome! If you encounter any issues or have suggestions for improvements, please open an issue on the GitHub repository. You can also submit pull requests with proposed changes.

# License
The Gems plugin is open-source and released under the MIT License. Feel free to modify and distribute the plugin as per the license terms.

# Credits
The Gems plugin was developed by Kingbobs and is maintained with the help of contributors.

Enjoy the exciting and enhanced gameplay experience with the Gems plugin on your PocketMine-MP server!
