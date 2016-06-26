<?php

namespace PartyGame;

/* Partygame Plugin
Plugin By EmreTr1
Status: INDEV    */

use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\level\sound\FizzSound;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\BatSound;
use pocketmine\level\sound\LaunchSound;
use pocketmine\level\sound\PopSound;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Level;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;

class PartyGame extends PluginBase implements Listener
{
	public $prefix="§8<§6Party§dGame§8>§r ";
	public $n;
	
	#MINIGAME SETTINGS
	public $dropwars;
	public $blockwars;
		
    public function OnEnable(){
			    $this->getServer()->getPluginManager()->registerEvents($this, $this);
                $this->getServer()->getLogger()->info("$this->prefix §aPlugin Has been Enabled!");
				@mkdir($this->getDataFolder());
				$this->config=new Config($this->getDataFolder() . "config.yml", Config::YAML);
				$this->config->save();
				$this->players=array();
				}
        
        public function OnLoad() {
                $this->getServer()->getLogger()->info("$this->prefix §ePlugin Loaded!");
        }
        
        public function OnDisable() {
                $this->getLogger()->info("$this->prefix §cPlugin Has been Disabled!");
				$this->saveDefaultConfig();
        }
        
        /*public function onHold(PlayerItemHeldEvent $event){
            $player = $event->getPlayer();
            if($event->getItem()->getId() == 46){
                $player->sendPopup(TextFormat:: AQUA . $this->prefix."Your Inventory Clearing...");
                $player->getInventory()->clearAll();
            }
        }*/
        
		public function OnCommand(CommandSender $s, Command $cmd, $label, array $args){
			switch($args[0]){
				case "help":
				    $s->sendMessage("§7============ $this->prefix §6HELP §7============");
					$s->sendMessage("§e/pg help:§a Show The help page");
					$s->sendMessage("§e/pg invite <PartyName> <name>:§a Invite player to Party");
					$s->sendMessage("§e/pg create <name>:§a Create a Party");
					$s->sendMessage("§e/pg out <PartyName> <name>:§a Out player the Party");
					$s->sendMessage("§e/pg play <PartyName> <dropwars|blockwars>:§a Play a minigame with friends");
					$s->sendMessage("§7============ $this->prefix §6HELP §7============");
					break;
				case "invite":
				    if((!empty($args[1])) and (!empty($args[2])) and $this->getServer()->getPlayer($args[2])){
						if($this->config->getNested("Partys.$args[1]")){
							$s->sendMessage($this->prefix."§a$args[2] was invited to $args[1] Party!");
							$joinplayer=$this->getServer()->getPlayer($args[2]);
							$joinplayer->sendMessage("§aYou §cwas invited the §6$args[1] Party!");
							$this->players[$args[1]][$args[2]]=array("id"=>$s->getName());
							$this->config->setNested("Partys.$args[1].Players", 1);
							$this->config->save();
						}else{
							$s->sendMessage($this->prefix."§cThis party or player not found!");
						}
					}else{
							$s->sendMessage($this->prefix."§eUsage: /pg invite <PartyName> <name>");
						}
					break;
				case "create":
				    if((!empty($args[1]))){
						if(!$this->config->getNested("Partys.$args[1]")){
							$ga=$args[1];
							$name=$s->getName();
						        $this->players[$ga][$name]=array("id"=>$name);
							$adet=0;
							$this->config->setNested("Partys.$args[1].Players", $adet);
							$this->config->save();
						    $s->sendMessage($this->prefix."§aYour Party Created!");
							$s->sendMessage($this->prefix."§bYour Party Name: $args[1]");
							$s->sendMessage($this->prefix."§eIf you want to change: /pg set name <old name> <new name>");
						}else{
							$s->sendMessage($this->prefix."§eUsage: /pg create <name>");
						}
					}
					break;
				case "out":
				case "left":
				    if((!empty($args[1])) and (!empty($args[2])) and $this->getServer()->getPlayer($args[2])){
						if($this->config->getNested("Partys.$args[1]")){
							unset($this->players[$args[1][$args[2]]]);
							$this->config->setNested("Partys.$args[1].Players", $this-players[$args[1]]);
							$this->config->save();
							$s->sendMessage($this->prefix."§a$args[2] §6was left the $args[1] Party!");
							$leftplayer=$this->getServer()->getPlayer($args[2]);
							$leftplayer->sendPopUp("§aYou §cwas left the §6$args[1] Party!");
						}else{
							$s->sendMessage($this->prefix."§eUsage: /pg out <PartyName> <name>");
						}
					}
					break;
				case "play":
				    if(!empty($args[1]) and (!empty($args[2]))){
						if($this->config->getNested("Partys.$args[1]")){
							if(strtolower($args[2])=="dropwars" or strtolower($args[2])=="blockwars"){
								if(strtolower($args[2])=="dropwars"){
									$this->dropwars[$args[1]]=1;
									$game=$args[1];
									$g=new CountDownTask($this, $game);
		                                                        $h=$this->getServer()->getScheduler()->scheduleRepeatingTask($g, 20);
		                                                        $g->setHandler($h);
									$s->sendMessage($this->prefix."§aDropWars Game Starting on $args[1]");
								}
							}
						}
					}
			}
		}
		#MINIGAME: BLOCKWARS
        public function OnInteract(PlayerInteractEvent $event){
        }
        #MINIGAME: DROPWARS
        public function OnDrop(PlayerDropItemEvent $event){
        }
        
		public function OnChat(PlayerChatEvent $event){
        }		
}

class CountDownTask extends PluginTask
{
	public $prefix="§8<§6Party§dGame§8>§r ";
	
	#MINIGAME SETTINGS
	public $gametime;
	public $time = 30;
	
	public function __construct(Plugin $plugin, $game){
		parent::__construct($plugin);
		$this->main=$plugin;
		$this->game=$game;
	}
		
        public function OnRun($currentTick){
			$game=$this->game;
			if($this->main->dropwars[$game]==1){
				foreach($this->main->players[$game] as $pl){
					$p=$this->main->getServer()->getPlayer($pl["id"]);
					$this->time--;
				    $p->sendTip("§bDropWars starting ". $this->time ." §6Seconds.");
				    if($this->time==0){
						$p->getLevel()->addSound(new PopSound($p));
						$p->sendPopUp("§dDROPWARS HAS BEEN STARTED!");
						$this->gametime--;
						$p->sendTip("\n\n\n§a$this->gametime §6seconds left");
						if($this->gametime==0){
							$p->sendPopUp("§6DROPWARS HAS BEEN FINISHED!");
						}
					}
				}
			}
		}		
}
