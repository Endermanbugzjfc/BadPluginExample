<?php

// Main class and core programme of ThonkingWildSpawn plugin

/*

     					_________	  ______________		
     				   /        /_____|_           /
					  /————/   /        |  _______/_____    
						  /   /_     ___| |_____       /
						 /   /__|    ||    ____/______/
						/   /    \   ||   |   |   
					   /__________\  | \   \  |
					       /        /   \   \ |
						  /________/     \___\|______
						                   |         \ 
							  PRODUCTION   \__________\	

							   翡翠出品 。 正宗廢品  
 
*/
							   
/* Portals:
	For all the API reference, please go to >> https://jenkins.pmmp.io/job/PocketMine-MP-doc/doxygen

	Get epic coders or people's help from the forum >> https://forums.pmmp.io/forums/development/

	Coding SOS / Emegency (100% non-emegency) coding help >> https://discord.gg/DXt4ht
 */

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

use pocketmine\plugin\PluginBase as pm;
use pocketmine\{Player};
use pocketmine\utils\{Config, TextFormat as TF, Terminal as C};
use pocketmine\command\{Command, CommandSender};
use pocketmine\event\Listener;
use pocketmine\block\{Bed};
use pocketmine\level\{Level, Position as Pos};
use pocketmine\math\{Vector3, AxisAlignedBB as BB};
use pocketmine\event\player\{PlayerRespawnEvent, PlayerInteractEvent, PlayerJoinEvent};
use pocketmine\event\entity\{EntityLevelChangeEvent};
use pocketmine\event\level\{LevelLoadEvent};
use pocketmine\event\block\{BlockBreakEvent};
use pocketmine\network\mcpe\protocol\{ActorEventPacket};
use pocketmine\item\{Item};

use Endermanbugzjfc\ThonkingWildSpawn\{GenerateBufferTask as GenBuffer};
use dktapps\pmforms\{CustomForm,CustomFormResponse, MenuForm, MenuOption, FormIcon, ModalForm};
use dktapps\pmforms\element\{Dropdown, Input, StepSlider, Toggle, Label};

class ThonkingWildSpawn extends pm implements Listener {

	public function onEnable() {
		$this->config = (new Config($this->getDataFolder() . "config.yml", Config::YAML));
		if (empty($this->config->get("cfv"))) {
			$this->getLogger()->info(TF::YELLOW . "Initialling system configure data file " . TF::GOLD . "(" . $this->getDataFolder() . "config.yml)" . TF::RESET);
			$this->config->set("worldMask", []);
			$this->config->set("displayLocation", []);
			$this->config->set("vanillaBed", []);
			$this->config->set("firstJoinTp", []);
			$this->config->set("prefix", TF::BOLD . TF::AQUA . " [" . TF::GREEN . "WildSpawn" . TF::AQUA . "] " . TF::RESET);
			$this->config->set("popup", [0 => "", 1 => "", 2 => "", 3 => "20:20:20"]);
			$this->config->set("totemAnimation", false);
			$this->config->set("disatance", [-3000, 350, -3000, 350]);
			$this->config->set("tpMsg", "{PREFIX} &eYou spawned at &6{X}, {Y}&r");
			$this->config->set("timeout", 8);
			$this->config->set("cfv", 1);
		}
		$this->world = gettype($this->config->get("worldMask")) === "array" ? $this->config->get("worldMask") : [];
		$this->display = gettype($this->config->get("displayLocation")) === "array" ? $this->config->get("displayLocation") : [];
		$this->vbed = gettype($this->config->get("vanillaBed")) === "array" ? $this->config->get("vanillaBed") : [];
		$this->distance = gettype($this->config->get("distance")) === "array" ? $this->config->get("distance") : [-3000, 350, -3000, 350];
		$this->fjtp = gettype($this->config->get("firstJoinTp")) === "array" ? $this->config->get("firstJoinTp") : [];
		$ph = gettype($this->config->get("popup")) === "array" ? $this->config->get("popup") : [0 => "", 1 => "", 2 => "", 3 => "20:20:20"];
		foreach ($ph as $i => $t) {
			if ($i === 3) {
				continue;
			}
			$ph[$i] = TF::colorize($t, "&");
		}
		$this->popup = $ph;
		$this->timeout = intval($this->config->get("timeout")) <= 0 ? 1 : intval($this->config->get("timeout"));
		$this->prefix = TF::colorize(strval($this->config->get("prefix")), "&");
		$this->totem = boolval($this->config->get("totemAnimation"));
		$this->kbcl = boolval($this->config->get("keepBufferChunkLoading"));
		$this->tpmsg = strval($this->config->get("tpMsg"));
		$this->mjoin = (new Config($this->getDataFolder() . "memory_join.yml", Config::YAML));
		$this->newchunk = [];
		$this->buffer = [];
		if (empty($this->mlist)) {
			$this->mlist = [];
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$w = $this->getServer()->getDefaultLevel();

		$this->buffer[strval($w->getId())] = $this->getScheduler()->scheduleTask(new GenerateBufferTask($this, $w));
		return;



		echo "\n\n";
		echo C::$COLOR_AQUA . "    ======= " . C::$COLOR_GREEN . "WhildSpawn" . C::$COLOR_AQUA . " =======\n" . C::$COLOR_AQUA . "    Status: " . C::$COLOR_GREEN . "Enabled\n" . C::$COLOR_YELLOW . "    Developer: " . C::$COLOR_LIGHT_PURPLE . "Enderman" . C::$COLOR_GREEN . "bugzjfc\n" . C::$COLOR_RED . "    You" . C::$COLOR_WHITE . "Tube: " . C::$COLOR_DARK_AQUA . "https://www.youtube.com/channel/UCD4OW4HGfWcDpfTvqypyYUw?sub_confirmation=1\n" . C::$COLOR_GOLD . "    Omlet: " . C::$COLOR_DARK_AQUA . "https://omlet.gg/profile/endermanbug_zjfc\n" . C::$COLOR_AQUA . "    ==========================\n\n" . C::$FORMAT_RESET;
		return;
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
		$cmd = strtolower($cmd->getName());
		if ($cmd === "wildspawn" || $cmd === "randomspawn" || $cmd === "ranspawn") {
			if (!$sender instanceof Player) {
				$sender->sendMessage(TF::RED . "Sorry, you can only use this command in-game" . TF::RESET);
				return true;
			}
			$this->overviewForm($sender);
			return true;
		}
		return true;
	}

	public function wildSpawnSetSpawn(PlayerInteractEvent $e) {

		if ($e->isCancelled()) return;

		$b = $e->getBlock();
		$p = $e->getPlayer();
		// $xid = $p->getUniqueId();

		if (!$b instanceof Bed) return;

		if (!in_array($b->getLevel()->getFolderName(), $this->vbed)) {
			return;
		}

		// $bp = ["x""y"]

		$p->setSpawn(new Pos($b->getX(), $b->getY(), $b->getZ(), $b->getLevel()));
		$p->sendMessage("tile.bed.respawnSet");
	}

	public function wildSpawnRespawn(PlayerRespawnEvent $e) {

		$p = $e->getPlayer();
		$f = $this->rtp($p);
		if (!$f instanceof Pos) return;

	    $e->setRespawnPosition($f);
	    $this->tpMsg($p, $f);
	    return;
	}

	public function onDisable() {

		$t = [];
		foreach (gettype($this->config->get("worldMask")) === "array" ? $this->config->get("worldMask") : [] as $i) {
			array_push($t, $i);
		}
		$this->config->set("worldMask", $t);
		$t = [];
		foreach (gettype($this->config->get("displayLocation")) === "array" ? $this->config->get("displayLocation") : [] as $i) {
			array_push($t, $i);
		}
		$this->config->set("displayLocation", $t);
		$t = [];
		foreach (gettype($this->config->get("vanillaBed")) === "array" ? $this->config->get("vanillaBed") : [] as $i) {
			array_push($t, $i);
		}
		$this->config->set("vanillaBed", $t);
		$t = [];
		foreach (gettype($this->config->get("firstJoinTp")) === "array" ? $this->config->get("firstJoinTp") : [] as $i) {
			array_push($t, $i);
		}
		$this->config->set("firstJoinTp", $t);
		$this->config->save();
		$this->config->reload();
		return;
	}

	private function infoForm(Player $p) {
		if (!$p->hasPermission("wildspawn.check")) {
			$p->sendMessage(TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET);
			return;
		}
		$form = (new MenuForm(
			TF::BOLD . TF::AQUA . "Plugin " . TF::GREEN . "information" . TF::RESET,
			TF::BOLD . TF::AQUA . "\nThonkingWildSpawn" . TF::RESET . TF::GREEN . "\nBy " . TF::LIGHT_PURPLE . "Enderman" . TF::GREEN . "bugzjfc\n" . TF::RESET . TF::DARK_RED . "\nYou" . TF::WHITE . "Tube: " . TF::BLUE . "\nhttps://www.youtube.com/channel/UCD4OW4HGfWcDpfTvqypyYUw?sub_confirmation=1\n" . TF::GOLD . "\nOmlet: " . TF::BLUE . "\nhttps://omlet.gg/profile/endermanbug_zjfc" . TF::GOLD . "\n\nA random spawn plugin for " . TF::DARK_AQUA . "PM" . TF::YELLOW . "3" . TF::DARK_GRAY . "\n(Tested compatibility on 3.13.0 Build 1807 and 3.12.6 Build 1806)\n\n" . TF::BOLD . TF::AQUA . "Used libraries / virions: \n" . TF::RESET . TF::WHITE . "- " . TF::GOLD . "PmForms by dktapps\n" . TF::WHITE . "- " . TF::GOLD . "Wildness by muqsit (Source code reference)\n\n" . TF::BOLD . TF::AQUA . "Update details: \n" . TF::RESET . TF::WHITE . "- " . TF::GOLD . "Version: 1.2.2\n" . TF::WHITE . "- " . TF::GOLD . "Fixed plugin still crashes in servers that with API version before 3.13\n" . TF::WHITE . "- " . TF::GOLD . "Added back API version 3.12 supports\n" . TF::WHITE . "- " . TF::GOLD . "Fixed server crashes after the plugin being disabled\n" . RESET,
			[new MenuOption(TF::BOLD . TF::DARK_AQUA . "Done" . TF::RESET)],
			function (Player $p, int $d): void {
				$this->overviewForm($p);
				return;
			},
			function (Player $p): void {
				$this->overviewForm($p);
				return;
			}
		));
		$p->sendForm($form);
	}

	public function wildSpawnMJoin(PlayerJoinEvent $e) {

		$p = $e->getPlayer();
		$n = $p->getName();
		$w = $p->getLevel();
		$wn = $w->getFolderName();
		$ws = $w->getSpawnLocation();
		$ml = $this->mjoin->get($wn);

		if (!is_array($ml)) {
			$ml = [];
		}

		if (!in_array($n, $ml)) {
			$p->setSpawn($ws->asVector3());
			if (in_array($w->getFolderName(), $this->fjtp)) {
				$f = $this->rtp($p);
				if ($f instanceof Pos) {
					$p->teleport($f);
					$this->tpMsg($p, $f);
				}
			}
			if (boolval($this->totem)) {
				$this->totemAnimation($p);
			}
			array_push($ml, $n);
			$this->mjoin->set($wn, $ml);
			$this->mjoin->save();
			$this->mjoin->reload();
		}
		return;
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

	public function rtp(Player $p) {

		$w = $p->getLevel();
		$n = $p->getName();
		$ps = $p->getSpawn();
		$ws = $w->getSpawnLocation();
		$bs = $w->getBlock($ps->asVector3());

		if (!in_array($w->getFolderName(), $this->world)) return false;

		if (!$ps->equals($ws->asVector3())) {
			if (in_array($w->getFolderName(), $this->vbed)) {
				if (!$bs instanceof Bed) {
					$p->setSpawn($ws->asVector3());
				}
			}
			return false;
		}

	    while (!isset($this->buffer[strval($w->getId())])) {
	    	$this->getScheduler()->scheduleTask(new GenerateBufferTask($this, $w));
	    }
	    $eb = $this->buffer[strval($w->getId())];
	    unset($this->buffer[strval($w->getId())]);
	    $this->getScheduler()->scheduleTask(new GenerateBufferTask($this, $w));

	    if (!$eb[1]) array_push($this->newchunk, $n);
	    return $eb[0];

	}

	private function tpMsg(Player $p, Pos $f) {
		$es = $this->popup[3];
		$es = explode(":", $es);
		$msg = [0 => $this->popup[0] ?? 20, 1 => $this->popup[1] ?? 20, 2 => $this->popup[2] ?? 20];
		$msg = [0 => $this->insertion($p, $msg[0], $f), 1 => $this->insertion($p, $msg[1], $f), 2 => $this->insertion($p, $msg[2], $f)];
		$p->sendMessage(strval($this->insertion($p, $this->tpmsg, $f)));
		$ver = explode(".", strval($this->getServer()->getVersion()));
		if (!isset($ver[1])) {
			throw new \RuntimeException("Unsupported API version.");
			return;
		}
		if (intval($ver[1]) >= 13) {
			$p->addTitle(strval($msg[0]), strval($msg[1]), intval($es[0]), intval($es[1]), intval($es[2]));
		} else {
			$p->sendTitle(strval($msg[0]), strval($msg[1]), intval($es[0]), intval($es[1]), intval($es[2]));
		}
		$p->sendPopup($msg[2]);
		return;
	}

	private function insertion(Player $p, string $t = "", Pos $f) {
		$w = $p->getLevel();

		$dfs = intval($f->distance($p->getLevel()->getSpawnLocation()->asVector3()));
		$nbe = $w->getNearbyEntities(new BB(floatval(floatval($f->getX()) - 16), 0, floatval(floatval($f->getZ()) - 16), floatval(floatval($f->getX()) + 16), 300, floatval(floatval($f->getZ()) + 16)));
		$nba = 0;
		foreach ($nbe as $i => $v) {
			if ($nbe instanceof Player) $nba = intval(intval($nba) + 1);
		}
		$sob = $w->getBlockAt($f->getFloorX(), intval(intval($f->getFloorY()) - 2), $f->getFloorZ());

		if (in_array($p->getName(), $this->newchunk)) {
			$t = str_replace("{IS_CHUNK_NEW}", "Yes", $t);
			unset($this->newchunk[array_search($p->getName(), $this->newchunk)]);
		}
		else {
			$t = str_replace("{IS_CHUNK_NEW}", "No", $t);
		}
		$t = str_replace("{X}", intval($f->getFloorX()), $t);
		$t = str_replace("{Z}", intval($f->getFloorZ()), $t);
		$t = str_replace("{Y}", intval($f->getFloorY()), $t);
		$t = str_replace("{PLAYER}", strval($p->getName()), $t);
		$t = str_replace("{WORLD}", strval($p->getLevel()->getName()), $t);
		$t = str_replace("{PREFIX}", strval($this->prefix), $t);
		$t = str_replace("{SPAWN_DISTANCE}", strval($dfs), $t);
		$t = str_replace("{BIOME}", strval($p->getLevel()->getBiome($f->getFloorX(), $f->getFloorZ())->getName()), $t);
		$t = str_replace("{NEARBY_PLAYERS}", strval($nba), $t);
		$t = str_replace("{STANDING_ON}", isset($sob) ? $sob->getName() : "Air", $t);
		return $t;
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
		$b[1] = (new Label("tips", TF::AQUA . "Available insertion tags: \n{X}, {Z}, {PLAYER}, {WORLD}, {IS_CHUNK_NEW}, {BIOME}, {NEARBY_PLAYERS}, {STANDING_ON}, {SPAWN_DISTANCE}, {PREFIX}" . TF::RESET));
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

	private function totemAnimation(Player $p) {
		$inv = $p->getInventory();
		$item = $inv->getItemInHand();
		$inv->setItemInHand(Item::get(450, 0, 1));
		$p->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
		$inv->setItemInHand($item);
		return;
	}

	private function distanceForm(Player $p, int $adm = 0) {
		if (!$p->hasPermission("wildspawn.check")) {
			$p->sendMessage(TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.check\" to do this action." . TF::RESET);
			return;
		}
		$b = [];
		switch ($adm) {
			case 1:
				$lt = TF::BOLD . TF::DARK_RED . "Action forbidden!\n\n" . TF::RED . "You are lacking the permission \"wildspawn.mod\" to do this action." . TF::RESET;
				break;

			case 2:
				$lt = TF::BOLD . TF::GREEN . "All changes have been saved!" . TF::RESET;
				break;
			
			default:
				$lt = TF::AQUA . "(*This is not the distance from the player's death position but from the original world spawnpoint)" . TF::RESET;
				break;
		}
		$b[0] = (new Label("additional", $lt));
		$b[1] = (new Input("xMax", TF::AQUA . "West (-X)" . TF::RESET, "Enter the interger of block distance", strval(intval($this->distance[0]))));
		$b[2] = (new Input("xMin", TF::AQUA . "East (+X)" . TF::RESET, "Enter the interger of block distance", strval(intval($this->distance[1]))));
		$b[3] = (new Input("zMax", TF::AQUA . "North (-Z)" . TF::RESET, "Enter the interger of block distance", strval(intval($this->distance[2]))));
		$b[4] = (new Input("zMin", TF::AQUA . "South (+Z)" . TF::RESET, "Enter the interger of block distance", strval(intval($this->distance[3]))));
		$b[5] = (new StepSlider("action", "", [
			TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
			TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
		]));
		if ($p->hasPermission("wildspawn.mod")) {
			if ($adm === 0) {
				$b[5] = (new StepSlider("action", "", [
				TF::BOLD . TF::RED . "    Cancel" . TF::RESET . TF::DARK_GRAY . " ===> " . TF::GRAY . "Apply" . TF::RESET,
				TF::GRAY . "    Cancel" . TF::RESET . TF::DARK_GRAY . " <=== " . TF::BOLD . TF::GREEN . "Apply" . TF::RESET
				], 1));
			}
		}
		$form = (new CustomForm(TF::BOLD . TF::AQUA . "TP" . TF::GREEN . " distance" . TF::RESET, $b,
			function (Player $p, CustomFormResponse $d) : void {
				if ($d->getInt("action") == 0) {
					$this->configForm($p);
					return;
				}
				if (!$p->hasPermission("wildspawn.mod")) {
					$this->distanceForm($p, 1);
					return;
				}
				$this->distance = [0 => -intval(str_replace("-", "", $d->getString("xMax") ?? 350)), intval(str_replace("-", "", $d->getString("xMin") ?? -3000)), -intval(str_replace("-", "", $d->getString("zMin") ?? -3000)), intval(str_replace("-", "", $d->getString("zMax") ?? 350))];
				$this->config->set("distance", $this->distance);
				$this->config->save();
				$this->config->reload();
				$this->distanceForm($p, 2);
				return;
			}, function(Player $p) : void {
				$this->configForm($p);
				return;
			}
		));
		$p->sendForm($form);
	}

	public function wildSpawnWorldLoads(LevelLoadEvent $e) {

		$w = $e->getLevel();

		$this->getScheduler()->scheduleTask(new GenerateBufferTask($this, $w));
		return;
	}

	public function wildSpawnWorldTp(EntityLevelChangeEvent $e) {

		$p = $e->getEntity();

		if (!$p instanceof Player) return;
		$w = $e->getTarget();
		$ml = $this->mjoin->get($w->getFolderName());
		if (in_array($p->getName(), $ml)) return;

		$f = $this->rtp($p);
		if (!$f instanceof Pos) return;
		$p->teleport($f);
		$this->tpMsg($p, $f);
		array_push($ml, $p->getName());
		$this->mjoin->set($w->getFolderName(), $ml);
		$this->mjoin->save();
		$this->mjoin->reload();
		return;

	}

	public function setBuffer(Pos $f, bool $icg, GenBuffer $instance) {

		if (!$instance instanceof GenBuffer) return;

		$this->buffer[strval($f->getLevel()->getId())] = [$f, $icg];
		return;
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
		foreach (scandir($this->getServer()->getDataPath()."worlds") as $n) {
			if ($n === "." || $n === "..");
			else {
				$w = $this->getServer()->getLevelByName($n);
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
		foreach (scandir($this->getServer()->getDataPath()."worlds") as $n) {

			if ($n === "." || $n === "..") continue;
			if (stripos($n, $k) !== false) {
				array_push($r, $n);
				continue;
			}
			$w = $this->getServer()->getLevelByName($n);
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
			$w = $this->getServer()->getLevelByName($n);
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
		$w = $this->getServer()->getLevelByName($n);
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

	public function getPrefix() : string {return $this->prefix;}
	public function getPopup() : array {return $this->popup;}
	public function getMsg() : string {return $this->tpmsg;}
	public function getDistance() : array {return $this->distance;}
	public function getTimeout() : int {return $this->timeout;}
}