<?php

// A task class of position buffer generation

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

use pocketmine\scheduler\{AsyncTask};
use pocketmine\level\{Level, Position as Pos};

use Endermanbugzjfc\ThonkingWildSpawn\ThonkingWildSpawn as Main;

class GenerateBufferTask extends AsyncTask {

	public function __construct(Main $m, Level $w) {
		$this->main = $m;
		$this->level = $w;
		$this->distance = $this->main->getDistance();
		$this->timeout = intval($this->main->getTimeout());
		return;
	}

	public function onRun(int $currentTick) {

		if (!$this->main instanceof Main) return;

		$w = $this->level;
		$ws = $w->getSpawnLocation();
		$td = false;
		$to = intval(intval(time()) + $this->timeout);

		while (!$td) {
			if (time() > $to) {
				$this->main->setBuffer(new Pos($ws->getX(), $ws->getY(), $ws->getZ(), $w), false, $this);
				return;
			}
			$x = rand(intval($this->distance[0] ?? -3000), intval($this->distance[1] ?? 350));
	    	$z = rand(intval($this->distance[2] ?? -3000), intval($this->distance[3] ?? 350));

	    	$icg = true;
	    	if (!$w->isChunkGenerated($x, $z)) {
	    		$icg = false;
	    	}

	    	$w->loadChunk($x, $z);
	    	while (!$w->isChunkLoaded($x, $z)) {continue;}

	    	$y = $w->getHighestBlockAt($x, $z);

	    	if ($y === -1) {
	    		$to = intval(intval($to) + 1);
	    		$w->unloadChunk($x, $z);
	    		continue;
	    	}

	    	$b = $w->getBlockAt($x, $y, $z);
	    	if (!$b->isSolid()) {
	    		$to = intval(intval($to) + 1);
	    		$w->unloadChunk($x, $z);
	    		continue;
	    	}

	    	$td = true;

		}
	    $this->main->setBuffer(new Pos($x, floatval(floatval($y + 2)), $z, $w, $w), $icg, $this);
		return;
	}
}