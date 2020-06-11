<?php

// Library class of forms in the ThonkingWildSpawn plugin

/* Colourizes

	AQUA = "b"
	BLACK = "0"
	BLUE = "9"
	BOLD = "l"
	DARK_AQUA = "3"
	DARK_BLUE = "1"
	DARK_GRAY = "8"
	DARK_GREEN = "2"
	DARK_PURPLE = "5"	
	DARK_RED = "4"
	EOL = "\n"
	ESCAPE = "\xc2\xa7"
	GOLD = "6"
	GRAY = "7"
	GREEN = "a"
	ITALIC = "o"
	LIGHT_PURPLE = "d"
	OBFUSCATED = "k"
	RED = "c"
	RESET = "r"
	STRIKETHROUGH = "m"
	UNDERLINE = "n"
	WHITE = "f"
	YELLOW = "e"
 */

declare(strict_types=1);
namespace Endermanbugzjfc\ThonkingWildSpawn;

use pocketmine\{Player, utils\TextFormat as TF};

use dktapps\pmforms\{CustomForm,CustomFormResponse, MenuForm, MenuOption, FormIcon, ModalForm};
use dktapps\pmforms\element\{Dropdown, Input, StepSlider, Toggle, Label};

use Endermanbugzjfc\ThonkingWildSpawn\ThonkingWildSpawn as Main;

class FormInterface {

	public function __construct(Main $m) {

		$this->main = $m
		$this->config = $this->main->getPluginConfig();

		$this->vbed =
	}

	public function overviewForm(Player $p) {
		if (!$p->hasPermission("wildspawn.check")) {
			$p->sendMessage(TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET);
			return;
		}
		$b = [];
		$wl = [];
		$b[0] = (new MenuOption(TF::BOLD . TF::DARK_AQUA . "Plugin information" . TF::RESET, new FormIcon("https://i.imgur.com/Buqs4pY.png", FormIcon::IMAGE_TYPE_URL)));
		$b[1] = (new MenuOption(TF::BOLD . TF::RED . "Exit WildSpawn\nsetting UI" . TF::RESET, new FormIcon("https://i.imgur.com/vk6IBgP.png", FormIcon::IMAGE_TYPE_URL)));
		$b[2] = (new MenuOption(TF::BOLD . TF::BLUE . "Plugin\nglobal configures" . TF::RESET, new FormIcon("https://i.imgur.com/jA8AzQm.png", FormIcon::IMAGE_TYPE_URL)));
		$b[3] = (new MenuOption(TF::BOLD . TF::DARK_GREEN . "Search world" . TF::RESET, new FormIcon("https://i.imgur.com/bjMQuxG.png", FormIcon::IMAGE_TYPE_URL)));
		foreach (scandir($this->main->getServer()->getDataPath()."worlds") as $n) {
			if ($n === "." || $n === "..");
			else {
				$w = $this->main->getServer()->getLevelByName($n);
				if (!$w instanceof Level) {
					$wn = TF::BOLD . TF::DARK_PURPLE . "Unloaded world" . TF::RESET . "\n" . TF::BLUE . "(" . $n . ")" . TF::RESET . TF::RESET;
				}
				else {
					$wn = TF::BOLD . TF::DARK_BLUE . $w->getName() . TF::RESET;
				}
				array_push($b, new MenuOption($wn . "\n" . TF::BLUE . "(" . $n . ")" . TF::RESET, new FormIcon("https://i.imgur.com/QT19rOy.jpg", FormIcon::IMAGE_TYPE_URL)));
				array_push($wl, $n);
			}
		}
		$form = (new MenuForm(TF::BOLD . TF::AQUA . "Wild" . TF::GREEN . "Spawn" . TF::RESET, "",
			$b,
			function (Player $p, int $d) use ($wl) : void {
				switch ($d) {

					case 0:
						$this->infoForm($p);
						return;
						break;

					case 1:
						return;
						break;

					case 2:
						$this->configForm($p);
						break;

					case 3:
						$this->searchForm($p);
						return;
						break;
					
					default:
						$dc = intval(intval($d) - 4);
						if (!isset($wl[$dc])) {
							$p->sendMessage(TF::BOLD . TF::RED . "Plugin unexpected error" . TF::RESET);
							return;
						}
						$this->settingForm($p, $wl[$dc]);
						return;
						break;
				}
				return;
			}
		));
		$p->sendForm($form);
	}

	private function searchForm(Player $p, string $c = "", string $ti = TF::BOLD . TF::AQUA . "Search " . TF::GREEN . "world" . TF::RESET) {
		if (!$p->hasPermission("wildspawn.check")) {
			$p->sendMessage(TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET);
			return;
		}
		$form = (new CustomForm($ti,
			[new Input("input", TF::BOLD . TF::GOLD . "Please enter a keyword of the world name or world folder name: " . "" .  TF::RESET, "Leave here empty to back", $c), new Toggle("clear", TF::AQUA . "Clear search input" . TF::RESET)],
			function (Player $p, CustomFormResponse $d) : void {
				if (boolval($d->getBool("clear"))) {
					$this->searchForm($p);
					return;
				}
				$k = $d->getString("input");
				if ($k === "") {
					$this->overviewForm($p);
					return;
				}
				$this->resultForm($p, $k);
				return;
			},
			function (Player $p) : void {
				$this->overviewForm($p);
				return;
			}
		));
		$p->sendForm($form);
	}

	private function resultForm(Player $p, string $k) {
		if (!$p->hasPermission("wildspawn.check")) {
			$p->sendMessage(TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET);
			return;
		}
		$wl = [];
		$r = [];
		foreach (scandir($this->main->getServer()->getDataPath()."worlds") as $n) {

			if ($n === "." || $n === "..") continue;
			if (stripos($n, $k) !== false) {
				array_push($r, $n);
				continue;
			}
			$w = $this->main->getServer()->getLevelByName($n);
			if (isset($w)) {
				if (stripos($w->getName(), $k) !== false) {
					array_push($r, $n);
					continue;
				}
			}
			continue;
		}
		if ($r === []) {
			$this->searchForm($p, $k, TF::BOLD . TF::DARK_RED . "No result have found!" . TF::RESET);
			return;
		}
		$b = [];
		$b[0] = (new MenuOption(TF::BOLD . TF::DARK_AQUA . "Search again" . TF::RESET, new FormIcon("https://i.imgur.com/bjMQuxG.png", FormIcon::IMAGE_TYPE_URL)));
		$b[1] = (new MenuOption(TF::BOLD . TF::DARK_RED . "Back" . TF::RESET, new FormIcon("https://i.imgur.com/vk6IBgP.png", FormIcon::IMAGE_TYPE_URL)));
		$t = $r;
		$wl = [];
		foreach ($t as $n) {
			$w = $this->main->getServer()->getLevelByName($n);
			if (!isset($w)) {
				array_push($b, new MenuOption(TF::BOLD . TF::DARK_PURPLE . "Unloaded world" . TF::RESET . "\n" . TF::BLUE . "(" . $n . ")" . TF::RESET));
				array_push($wl, $n);
			}
			else {
				array_push($b, new MenuOption(TF::BOLD . TF::DARK_BLUE . $w->getName() . TF::RESET . "\n" . TF::BLUE . "(" . $n . ")" . TF::RESET));
				array_push($wl, $n);
			}
		}
		$form = (new MenuForm(TF::BOLD . TF::AQUA . count($wl) . TF::GREEN . " found result" . TF::RESET, "",
			$b,
			function (Player $p, int $d) use ($wl, $k) : void {
				switch ($d) {
					case 0:
						$this->searchForm($p, $k);
						return;
						break;

					case 1:
						$this->overviewForm($p);
						return;
						break;
					
					default:
						$dc = intval(intval($d) - 2);
						if (!isset($wl[$dc])) {
							$this->searchForm($p, $k, TF::BOLD . TF::DARK_RED . "Plugin unexpected error" . TF::RESET);
							return;
						}
						$this->settingForm($p, $wl[$dc]);
						return;
						break;
				}
				return;
			},
			function (Player $p) : void {
				$this->overviewForm($p);
				return;
			}
		));
		$p->sendForm($form);
	}

	private function settingForm(Player $p, string $n, int $adm = 0) {
		if (!$p->hasPermission("wildspawn.check")) {
			$p->sendMessage(TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET);
			return;
		}
		$wn = "Unloaded";
		$w = $this->main->getServer()->getLevelByName($n);
		if (isset($w)) {
			$wn = $w->getName();
		}
		$b = [];
		$at = TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.mod\" to do this action." . TF::RESET;
		if ($adm === 2) {
			$at = TF::BOLD . TF::GREEN . "All changes have been saved!" . TF::RESET;
		}
		$b[0] = (new Label("additional", $at));
		$b[1] = (new Label("info", TF::BOLD . TF::GOLD . "World: " . TF::RESET. TF::YELLOW . $wn . TF::BOLD . TF::GOLD . "\nFolder: " . TF::RESET . TF::YELLOW . $n . TF::RESET));
		$b[2] = (new Toggle("enable", TF::AQUA . "Enable wild spawn" . TF::RESET));
		$b[3] = (new Toggle("display", TF::AQUA . "Display location info after player respawn randomly" . TF::RESET));
		$b[4] = (new Toggle("vbed", TF::AQUA . "Enable vanilla set spawn feature" . TF::RESET));
		$b[5] = (new Label("vbed_label", TF::BOLD . TF::DARK_GRAY . "(Able to set spawn via bed at day, reset spawn on bed break...)"));
		$b[6] = (new Toggle("fjtp", TF::AQUA . "Teleport on player first join" . TF::RESET));
		$b[7] = (new StepSlider("action", "", [
			TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
			TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
			]));
		if ($adm === 0) {
			unset($b[0]);
		}
		if (in_array($n, $this->world)) {
			$b[2] = (new Toggle("enable", TF::AQUA . "Enable wild spawn" . TF::RESET, true));
		}
		if (in_array($n, $this->display)) {
			$b[3] = (new Toggle("display", TF::AQUA . "Display location info after player respawn randomly" . TF::RESET, true));
		}
		if (in_array($n, $this->vbed)) {
			$b[4] = (new Toggle("vbed", TF::AQUA . "Enable vanilla set spawn feature" . TF::RESET, true));
		}
		if (in_array($n, $this->fjtp)) {
			$b[6] = (new Toggle("fjtp", TF::AQUA . "Teleport on player first join" . TF::RESET, true));
		}
		if ($p->hasPermission("wildspawn.mod")) {
			$b[7] = (new StepSlider("action", "", [
			TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
			TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
			], 1));	
		}
		if ($adm === 2) {
			$b[7] = (new StepSlider("action", "", [
			TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
			TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
			]));
		}
		$form = (new CustomForm(TF::BOLD . TF::AQUA . "World " . TF::GREEN . "setting" . TF::RESET, $b,
			function(Player $p, CustomFormResponse $d) use ($n): void {
				if ($d->getInt("action") == 0) {
					$this->overviewForm($p);
					return;
				}
				if (!$p->hasPermission("wildspawn.mod")) {
					$this->settingForm($p, $n, 1);
					return;
				}
				if (boolval($d->getBool("enable"))) {
					if (!in_array($n, $this->world)) {
						array_push($this->world, $n);
					}
				}
				else {
					if (in_array($n, $this->world)) {
						unset($this->world[array_search($n, $this->world)]);
					}
				}
				$this->config->set("worldMask", $this->world);
				if (boolval($d->getBool("display"))) {
					if (!in_array($n, $this->display)) {
						array_push($this->display, $n);
					}
				}
				else {
					if (in_array($n, $this->display)) {
						unset($this->display[array_search($n, $this->display)]);
					}
				}
				$this->config->set("displayLocation", $this->display);
				if (boolval($d->getBool("vbed"))) {
					if (!in_array($n, $this->vbed)) {
						array_push($this->vbed, $n);
					}
				}
				else {
					if (in_array($n, $this->vbed)) {
						unset($this->vbed[array_search($n, $this->vbed)]);
					}
				}
				$this->config->set("vanillaBed", $this->vbed);
				if (boolval($d->getBool("fjtp"))) {
					if (!in_array($n, $this->fjtp)) {
						array_push($this->fjtp, $n);
					}
				}
				else {
					if (in_array($n, $this->fjtp)) {
						unset($this->fjtp[array_search($n, $this->fjtp)]);
					}
				}
				$this->config->set("firstJoinTp", $this->fjtp);
				$this->config->save();
				$this->config->reload();
				$this->settingForm($p, $n, 2);
				return;
			},
			function(Player $p) : void {
				$this->overviewForm($p);
				return;
			}
		));
		$p->sendForm($form);
	}
	
	private function prefixForm(Player $p, int $adm = 0) {
		if (!$p->hasPermission("wildspawn.check")) {
			$p->sendMessage(TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET);
			return;
		}
		switch ($adm) {
			case 1:
				$adt = TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.mod\" to do this action." . TF::RESET;
				break;

			case 2:
				$adt = TF::BOLD . TF::GREEN . "All changes have been saved!" . TF::RESET;
				break;
			
			default:
				$adt = "";
				break;
		}
		$b = [];
		$b[0] = (new Label("additional", $adt));
		$b[1] = (new Input("input", TF::BOLD . TF::GOLD . "Edit plugin in-game feedback message prefix: " . TF::RESET, "Prefix here", $this->prefix));
		$b[2] = (new StepSlider("action", "", [
			TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
			TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
		]));
		if ($adm === 0) {
			unset($b[0]);
			$b[2] = (new StepSlider("action", "", [
			TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
			TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
			], 1));
		}
		if (!$p->hasPermission("wildspawn.mod")) {
			$b[2] = (new StepSlider("action", "", [
			TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
			TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
			]));
		}
		$form = (new CustomForm(TF::BOLD . TF::AQUA . "Set" . TF::GREEN . " Prefix" . TF::RESET,
			$b,
			function (Player $p, CustomFormResponse $d) : void {
				if (intval($d->getInt("action")) === 0) {
					$this->configForm($p);
					return;
				}
				if (!$p->hasPermission("wildspawn.mod")) {
					$this->prefixForm($p, 1);
					return;
				}
				$this->prefix = TF::colorize(strval($d->getString("input")), "&");
				$this->config->set("prefix", $this->prefix);
				$this->config->save();
				$this->config->reload();
				$this->prefixForm($p, 2);
				return;
			},
			function (Player $p) : void {
				$this->configForm($p);
				return;
			}
		));
		$p->sendForm($form);
	}
	
	private function configForm(Player $p) {
		if (!$p->hasPermission("wildspawn.check")) {
			$p->sendMessage(TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET);
			return;
		}

		$b = [];
		$b[0] = (new MenuOption(TF::BOLD . TF::DARK_RED . "Back" . TF::RESET, new FormIcon("https://i.imgur.com/vk6IBgP.png", FormIcon::IMAGE_TYPE_URL)));
		$b[1] = (new MenuOption(TF::BOLD . TF::DARK_AQUA . "In-game messages prefix" . TF::RESET, new FormIcon("https://i.imgur.com/YX2RTpX.png", FormIcon::IMAGE_TYPE_URL)));
		$b[2] = (new MenuOption(TF::BOLD . TF::LIGHT_PURPLE . "TP popups" . TF::RESET, new FormIcon("https://i.imgur.com/ryGimsT.png", FormIcon::IMAGE_TYPE_URL)));
		$b[3] = (new MenuOption(TF::BOLD . TF::DARK_BLUE . "First join\ntotem animation" . TF::RESET, new FormIcon("https://i.imgur.com/5E1OByo.png", FormIcon::IMAGE_TYPE_URL)));
		$b[4] = (new MenuOption(TF::BOLD . TF::DARK_GREEN . "Teleportation\ndistance" . TF::RESET, new FormIcon("https://i.imgur.com/y1rkvl2.png", FormIcon::IMAGE_TYPE_URL)));
		$form = (new MenuForm(TF::BOLD . TF::AQUA . "Global" . TF::GREEN . " configure" . TF::RESET, "", $b, function(Player $p, int $d) : void {
			switch ($d) {
				case 0:
					$this->overviewForm($p);
					return;
					break;

				case 1:
					$this->prefixForm($p);
					return;
					break;

				case 2:
					$this->popupForm($p);
					return;
					break;

				case 3:
					$this->totemForm($p);
					return;
					break;

				case 4:
					$this->distanceForm($p);
					return;
					break;
				
				default:
					$p->sendMessage(TF::BOLD . TF::RED . "Plugin unexpected error" . TF::RESET);
					return;
					break;
			}
			return;
		}, function(Player $p) : void {
			$this->overviewForm($p);
			return;
		}));
		$p->sendForm($form);
	}

	private function popupForm(Player $p, int $adm = 0) {
		if (!$p->hasPermission("wildspawn.check")) {
			$p->sendMessage(TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET);
			return;
		}
		$b = [];
		switch ($adm) {
			case 1:
				$at = TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.mod\" to do this action." . TF::RESET;
				break;
			
			default:
				$at = TF::BOLD . TF::GREEN . "All changes have been saved!" . TF::RESET;
				break;
		}
		if ($adm !== 0) {
			$b[0] = (new Label("additional", $at));
		}
		$es = gettype($this->popup[3]) === "string" ? $this->popup[3] : "20:20:20";
		$es = explode(":", str_replace("：", ":", $es));
		$es = [0 => intval($es[0]), 1 => intval($es[1]), 2 => intval($es[2])];
		$es = implode(":", $es);
		$b[1] = (new Label("tips", TF::AQUA . "Available insertion tags: \n{X}, {Z}, {PLAYER}, {WORLD}, {IS_CHUNK_NEW}" . TF::RESET));
		$b[2] = (new Input("title", TF::BOLD . TF::GOLD . "Title text: " . TF::RESET, "Empty here to disable", strval($this->popup[0])));
		$b[3] = (new Input("subtitle", TF::BOLD . TF::GOLD . "Subtitle text: " . TF::RESET, "Empty here to disable", strval($this->popup[1])));
		$b[4] = (new Input("effect", TF::AQUA . "Title effects: " . TF::RESET, "\"Fade in\" : \"Stay\" : \"Fade out\"", strval($es)));
		$b[5] = (new Label("effectTips", TF::AQUA . "(Effects are count in ticks,\nplease enter with the following valid format:\n\"Fade in\" : \"Stay\" : \"Fade out\",\nexample: \"5:20:15\")" . TF::RESET));
		$b[6] = (new Input("actionbar", TF::BOLD . TF::GOLD . "Actionbar text: " . TF::RESET, "Empty here to disable", strval($this->popup[2])));
		$b[7] = (new StepSlider("action", "", [
			TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
			TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
		]));
		if ($p->hasPermission("wildspawn.mod")) {
			if ($adm === 0) {
				$b[7] = (new StepSlider("action", "", [
				TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
				TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
				], 1));
			}
		}

		$form = (new CustomForm(TF::BOLD . TF::AQUA . "Popup" . TF::GREEN . " setting" . TF::RESET, $b,
			function (Player $p, CustomFormResponse $d) : void {
				if ($d->getInt("action") == 0) {
					$this->configForm($p);
					return;
				}
				if (!$p->hasPermission("wildspawn.mod")) {
					$this->popupForm($p, 1);
					return;
				}
				$es = $d->getString("effect") ?? "20:20:20";
				$es = explode(":", str_replace("：", ":", $es));
				$es = [$es[0], $es[1], $es[2]];
				$es = implode(":", $es);
				$this->popup = [0 => TF::colorize($d->getString("title") ?? "&r", "&"), 1 => TF::colorize($d->getString("subtitle"), "&"), 2 => TF::colorize($d->getString("actionbar"), "&"), 3 => $es];
				$this->config->set("popup", $this->popup);
				$this->config->save();
				$this->config->reload();
				$this->popupForm($p, 2);
				return;
			}, function(Player $p) : void {
				$this->configForm($p);
				return;
			}
		));
		$p->sendForm($form);
	}

	private function totemForm(Player $p) {
		if (!$p->hasPermission("wildspawn.check")) {
			$ti = TF::BOLD . TF::DARK_RED . "Action forbidden!" . TF::RESET;
			$tx = TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET;
			$bt = ["gui.ok", ""];
		}
		$ite = TF::BOLD . TF::RED . "Disabled." . TF::RESET;
		$itc = TF::BOLD . TF::GREEN . "Enable?" . TF::RESET;
		if (boolval($this->totem)) {
			$ti = "";
			$ite = TF::BOLD . TF::GREEN . "Enabled." . TF::RESET;
			$itc = TF::BOLD . TF::RED . "Disable?" . TF::RESET;
		}
		if (!$p->hasPermission("wildspawn.mod")) {
			$ti = "";
			$tx = TF::AQUA . "First joining totem animation is currently " . $ite . TF::RESET;
			$bt = ["gui.ok", ""];
		}
		else {
			$ti = "";
			$tx = TF::AQUA . "First joining totem animation is currently " . $ite . TF::GOLD . " Are you sure to change it to " . $itc . TF::RESET;
			$bt = ["gui.yes", "gui.no"];
		}
		$form = (new ModalForm($ti, $tx,
			function (Player $p, bool $d) : void{
				if (!$p->hasPermission("wildspawn.mod")) {
					$this->configForm($p);
					return;
				}
				if ($d) {
					if (!boolval($this->totem)) {
						$this->totem = true;
					}
					else {
						$this->totem = false;
					}
					$this->config->set("totemAnimation", $this->totem);
					$this->config->save();
					$this->config->reload();
					$this->totemForm($p);
					return;
				}
				$this->configForm($p);
				return;
			}, $bt[0], $bt[1]
		));
		$p->sendForm($form);
	}
}