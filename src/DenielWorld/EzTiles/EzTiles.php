<?php

namespace DenielWorld\EzTiles;

use DenielWorld\EzTiles\tile\ContainerTile;
use DenielWorld\EzTiles\tile\SimpleTile;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;

class EzTiles extends PluginBase{

    /** @var Plugin|null */
    protected static $registrant = null;

    /** @var bool */
    protected $loggerEnabled = true;

    public function onEnable()
    {
        if($this->loggerEnabled and self::$registrant == null) {
            self::$registrant = $this;
            try {
                self::init();
            } catch (\ReflectionException $e) {
                $this->getLogger()->error("Failed to register EzTiles tile classes, any plugins that use them will not work!");
            }

            $this->getLogger()->info(
                "You have enabled EzTiles as a plugin. Please keep in mind that EzTiles should be used as a virion for more features."
            );
        }
    }

    /**
     * Disables onEnable & onDisable logger warnings,
     * Warnings are automatically disabled if you use this as a virion
     * @param bool $value
     */
    public function disableLogger(bool $value) : void{
        $this->loggerEnabled = $value;
    }

    /**
     * Registers all existing custom tiles,
     * This has to be executed manually if you use this as a virion
     * @param bool $overwrite
     * @throws \ReflectionException
     */
    public static function init(bool $overwrite = false){
        Tile::registerTile(SimpleTile::class, ["simpleTile"]);
        Tile::registerTile(ContainerTile::class, ["containerTile"]);
        //More tile types are planned to be added in the future
    }

    /**
     * Sets the given plugin as the registrant, redirecting callable method string search towards itself
     * In other words, upon tile creation if an update is scheduled and a callable method string is provided...
     * ..., then the static method by that name will be looked for in the registrant
     * @param Plugin $registrant
     */
    public static function register(Plugin $registrant){
        self::$registrant = $registrant;
    }

    /**
     * This method is internally used for finding functions to call onUpdate() of tiles, do not change this
     * @return Plugin
     */
    public static function getRegistrant() : Plugin{
        return self::$registrant;
    }

    public function onDisable()
    {
        if($this->loggerEnabled and self::$registrant == null) $this->getLogger()->info("EzTiles has been disabled.");
    }

}