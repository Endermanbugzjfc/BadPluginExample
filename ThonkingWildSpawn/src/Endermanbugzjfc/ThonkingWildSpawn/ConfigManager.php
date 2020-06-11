<?php

// Data manager class of plugin configures

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
 
use pocketmine\utils\{Config, TextFormat as TF};

use Endermanbugzjfc\ThonkingWildSpawn\{ThonkingWildSpawn as Main};
 
class ConfigManager {

	public function __construct(Main $m) {

		$this->main = $m;
		$this->config = (new Config($this->getDataFolder() . "config.yml", Config::YAML));
		
		if (empty($this->config->get("cfv"))) {
			$this->main->getLogger()->info(TF::YELLOW . "Initialling system configure data file " . TF::GOLD . "(" . $this->getDataFolder() . "config.yml)" . TF::RESET);
			$this->config->set("cfv", 2);
			$this->config->set("local", [""]);
			$this->config->set("global", ["totemAnimation" => false, "bufferGenTimeout" => 8, "updatePreset" => true, "prefix" => TF::BOLD . TF::AQUA . " [" . TF::GREEN . "WildSpawn" . TF::AQUA . "] " . TF::RESET]);
			$this->config->set("preset", ["tpMsg" => "", "tpPopup"=> [0 => "", 1 => "", 2 => "", 3 => "20:20:20"], "distance" => [-3000, 350, -3000, 350]]);
			$this->config->set("tpMsg", ["{PREFIX} &eYou spawned at &6{X}, {Y}&r"]);
			$this->config->set("vanillaBed", []);
			$this->config->set("firstJoinTp", []);
			$this->config->set("prefix", TF::BOLD . TF::AQUA . " [" . TF::GREEN . "WildSpawn" . TF::AQUA . "] " . TF::RESET);
			$this->config->set("popup", [$this->main->getServer()->getDe => "", 1 => "", 2 => "", 3 => "20:20:20"]);
			$this->config->set("disatance", [0 => [-3000, 350, -3000, 350]]);
		}

		switch (intval($this->config->get("cfv"))) {
			case 1:
				$this->main->getLogger()->info(TF::YELLOW . "Updating system configure data file format version " . TF::GOLD . "from cfv-1 to cfv-2" . TF::YELLOW . ", this might take a while, " . TF::RED . "please do not kill the server" . TF::AQUA . " and wait patiently..." . TF::RESET);

				$kd = $this->config->getAll();
				foreach ($kd as $i => $v) {
					$this->config->remove($i);
				}
				foreach ($kd["worldMask"] as $w) {
					$this->config->setNested("local." . strval($w) . ".wildSpawn", true);
				}
				foreach ($kd["displayLocation"] as $w) {
					$this->config->setNested("local." . strval($w) . ".tpMsg", "{PREFIX} &eYou spawned at &6{X}, {Y}&r");
				}
				$this->config->set("global", ["totemAnimation" => false, "bufferGenTimeout" => 8, "updatePreset" => true, "prefix" => TF::BOLD . TF::AQUA . " [" . TF::GREEN . "WildSpawn" . TF::AQUA . "] " . TF::RESET]);
				$this->config->set("preset" . ["tpMsg" => "{PREFIX} &eYou spawned at &6{X}, {Y}&r", "popup" => $kd["popup"], "distance" => $kd["distance"]]);
				break;
		}
		// $this->world = gettype($this->config->get("wildSpawnWorld")) === "array" ? $this->config->get("wildSpawnWorld") : [];
		// $this->vbed = gettype($this->config->get("vanillaBed")) ? $this->config->get("vanillaBed") : [];
		// }
		// $this->world = gettype($this->config->get("wildSpawnWorld")) === "array" ? $this->config->get("wildSpawnWorld") : [];
		// $this->display = gettype($this->config->get("displayLocation")) === "array" ? $this->config->get("displayLocation") : [];
		// $this->vbed = gettype($this->config->get("vanillaBed")) === "array" ? $this->config->get("vanillaBed") : [];
		// $this->distance = gettype($this->config->get("distance")) === "array" ? $this->config->get("distance") : [-3000, 350, -3000, 350];
		// $this->fjtp = gettype($this->config->get("firstJoinTp")) === "array" ? $this->config->get("firstJoinTp") : [];
		// $dh = gettype($this->config->get("popup")) === "array" ? $this->config->get("popup") : [0 => "", 1 => "", 2 => "", 3 => "20:20:20"];
		// foreach ($dh as $dwn => $ph) {}
		// foreach ($ph as $i => $t) {
		// 	if ($i === 3) {
		// 		continue;
		// 	}
		// 	$ph[$i] = TF::colorize($t, "&");
		// }
		// $this->popup = $ph;
		// $this->timeout = intval($this->config->get("timeout")) <= 0 ? 1 : intval($this->config->get("timeout"));
		// $this->prefix = TF::colorize(strval($this->config->get("prefix")), "&");
		// $this->totem = boolval($this->config->get("totemAnimation"));
		// $this->tpmsg = strval($this->config->get("tpMsg"));
		$this->sysdb = (new Config($this->getDataFolder() . "system_db.yml", Config::YAML));
	}
	
	public function vBed(?string $s) {
		if (!isset($s)) return $this->vbed ?? [];
		array_push($this->vbed, $s);
		$this->config->set("vanillaBed", $this->vbed);
		return $this->vbed;
	}
	
	public function world(?string $s) {
		if (!isset($s)) return $this->world ?? [];
		array_push($this->world, $s);
		$this->config->set("wildSpawnWorld", $this->world);
	}
}