<h1 align="center">KygekJoinUI</h1>

<p align="center">
<a href="https://github.com/thebigcrafter/KygekJoinUI/releases"><img src="https://img.shields.io/github/downloads/thebigcrafter/KygekJoinUI/total?style=for-the-badge" alt="GitHub downloads" /></a>
<a href="https://github.com/thebigcrafter/KygekJoinUI/blob/main/LICENSE"><img src="https://img.shields.io/github/license/thebigcrafter/KygekJoinUI?style=for-the-badge" alt="license" /></a>
<a href="https://discord.gg/PykBfE2TZ9"><img src="https://img.shields.io/discord/1087729577004122112?color=7289DA&label=discord&logo=discord&style=for-the-badge" alt="discord" /></a>
</p>

# 📖 About

A plugin for Axolotl-PM (PocketMine-MP API 5) that shows an information and guide UI when players join the server.

# 🧩 Features

* Simple Form and Modal Form mode
* Easy to setup (plugin can be edited in config.yml)
* Config.yml updated automatically when a newer version is detected
* Config_old.yml will be created before the config.yml gets updated
* § can be used as formatting codes
* {line} can be used to break texts to a new line
* {player} can be used to display the player's name
* {online} can be used to display currently online players on the server
* {max_online} can be used to display maximum online players on the server
* {world} can be used to display the world name the KygekJoinUI shows
* Detects missing config file
* Command execute mode can be as console or player
* Add commands when player closes the KygekJoinUI by pressing the X button
* Automatic plugin update checker on server startup
* Add an image beside buttons
* You can set KygekJoinUI to only be shown to players joined the first time

# ⬇️ Installation

1. Download the latest plugin PHAR from [GitHub Releases](https://github.com/thebigcrafter/KygekJoinUI/releases).
2. Put `KygekJoinUI.phar` in the plugins folder.
3. Start or restart Axolotl-PM.
4. Edit the plugin in `plugin_data/KygekJoinUI/config.yml`.

# 🛠️ Development

Install the locked Composer dependencies and use [DevTools](https://github.com/NhanAZ/DevTools) to package and validate the plugin PHAR. The packaging process resolves the pinned FormsUI and Hydrogen virions from `.poggit.yml`; the build tool revision belongs to the verified build environment rather than this user-facing documentation.

```sh
composer install
```

# ⚠️ Notice

If you update the plugin, a file called config-old.yml will be generated. It will contain previously config.yml datas. You can restore the old data from there.  

# ⚖️ License

Licensed under the [GNU General Public License v3.0](https://github.com/thebigcrafter/KygekJoinUI/blob/main/LICENSE) license.
