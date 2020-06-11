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

use Endermanbugzjfc\ThonkingWildSpawn\{GenerateBufferTask as GenBuffer, ConfigManager};

class ThonkingWildSpawn extends pm implements Listener {

	public function onEnable() {
		$this->config = (new ConfigManager($this, new Config($this->getDataFolder() . "config.yml", Config::YAML)));

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
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$w = $this->getServer()->getDefaultLevel();

		$this->getServer()->getScheduler()->scheduleTask(new GenBuffer($this, $w));
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
		$in= (new MenuForm(
			TF::BOLD . TF::AQUA . "Plugin " . TF::GREEN . "information" . TF::RESET,
			TF::BOLD . TF::AQUA . "\nThonkingWildSpawn" . TF::RESET . TF::GREEN . "\nBy " . TF::LIGHT_PURPLE . "Enderman" . TF::GREEN . "bugzjfc\n" . TF::RESET . TF::DARK_RED . "\nYou" . TF::WHITE . "Tube: " . TF::BLUE . "\nhttps://www.youtube.com/channel/UCD4OW4HGfWcDpfTvqypyYUw?sub_confirmation=1\n" . TF::GOLD . "\nOmlet: " . TF::BLUE . "\nhttps://omlet.gg/profile/endermanbug_zjfc" . TF::GOLD . "\n\nA random spawn plugin for " . TF::DARK_AQUA . "PM" . TF::YELLOW . "3" . TF::DARK_GRAY . "\n(Tested compatibility on 3.12.3 Build 1798)\n\n" . TF::BOLD . TF::AQUA . "Used libraries / virions: \n" . TF::RESET . TF::WHITE . "- " . TF::GOLD . "PmForms by dktapps\n" . TF::WHITE . "- " . TF::GOLD . "Wildness by muqsit (Source code reference)\n\n" . TF::BOLD . TF::AQUA . "Update details: \n" . TF::RESET . TF::WHITE . "- " . TF::GOLD . "Version: 1.1.1\n" . TF::WHITE . "- " . TF::GOLD . "Optimized wildness teleportation system\n" . TF::WHITE . "- " . TF::GOLD . "Fixed first join tp malfunctioning and made it into a world option\n" . TF::WHITE . "- " . TF::GOLD . "Added first join totem animation option\n" . TF::WHITE . "- " . TF::GOLD . "Added teleportation popups\n" . TF::WHITE . "- " . TF::GOLD . "Fixed bugs\n" . TF::WHITE . "- " . TF::GOLD . "Bumped API to 3.12.3\n" . TF::WHITE . "- " . TF::GOLD . "Added teleportation distance options" . TF::RESET,
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
		$p->sendTitle(strval($msg[0]), strval($msg[1]), intval($es[0]), intval($es[1]), intval($es[2]));
		$p->sendPopup($msg[2]);
		return;
	}

	private function insertion(Player $p, string $t = "", Pos $f) {

		$dfs = intval($f->distance($p->getLevel()->getSpawnLocation()->asVector3()));
		$nbe = $w->getNearbyEntities(new BB(floatval(floatval($f->getX()) - 16), 0, floatval(floatval($f->getZ()) - 16), floatval(floatval($f->getX()) + 16), 300, floatval(floatval($f->getZ()) + 16)));
		$nba = 0;
		foreach ($nbe as $i => $v) {
			if ($nbe instanceof Player) $nba = intval(intval($nba) + 1);
		}
		$sob = $w->getBlockAt($f->getFloorX(), intval(intval($f->getFloorY) - 2), $f->getFloorZ());

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

	public function getPrefix() : string {return $this->prefix;}
	public function getPopup() : array {return $this->popup;}
	public function getMsg() : string {return $this->tpmsg;}
	public function getDistance() : array {return $this->distance;}
	public function getTimeout() : int {return $this->timeout;}
}
