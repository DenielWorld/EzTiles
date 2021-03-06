# EzTiles
This plugin/virion has been created in
order to help beginner developers create and manage their
tiles easier.

## What are tiles?

Tiles are basically block entities that pocketmine uses
to save block relevant data. Just like items, they have
an NBT which allows for effective data saving without having
to use an external database.

All data passed on to a tile is saved through restarts,
meaning that when you restart your server, the tiles that
have already been created will remain there, unless you
`close()` them, which basically deletes them. This is why
tiles are a preferred way to save block data.

The point of this plugin/virion hybrid is to allow 
for developers to create & manage tiles easier. Without
this virion, tiles have to first be created as a custom
class with some custom methods for saving tile data properly,
and they also have to be initialized using NBT/CompoundTag.
For a beginner developer, some of this stuff may be hard to
understand, which is why I have created this virion to
simplify the tile system for beginner developers.

## Information
* This has been created by @DenielWorld, who is mainly
a developer of private plugins.
* This was tested by @Heisenburger69, and was said to be
working just fine. Feel free to open an issue if you find
a bug, or if you want to request a feature.
* This is a tool for easier developing, not a functional 
by itself plugin, so don't expect it to magically add the
features you want.
* This is a public project which was created as a result
of my boredom, if it is used I'll try my best to keep it
updated, but I will not make any promises.
* Lots of information can be found by reading the source
code itself, if you feel like you won't need the documentation
below.
* This should be run as a virion, because as of now running
this as a plugin will prevent you from using some of
existent tile features. Although, this will be resolved
soon :D

## How to use

First of all, it is recommended to use this as a 
virion, if you want your tiles to have custom 
callable updates without having to modify the plugin
itself.

To use this as a virion, run this code in your PluginBase

```php
public function onEnable(){
    EzTiles::register($this);
}
```

Next step is to register the custom tiles, because
they won't be registered automatically if you
are running this as a virion

```php
EzTiles::init();
```

Now that you have setup the virion, you can create and
manage tiles much easier

As of now the only available custom tile is SimpleTile, which
you can create like this:

```php
$tileInfo = new TileInfo(new Position(0, 0, 0));
new SimpleTile(Server::getInstance()->getLevelByName("ExampleLevel"), $tileInfo);
```

The code above shows how to create the most basic simple tile,
which will simply exist at the given position and level

This was an overall explanation of how EzTiles work, if
you want a deeper explanation, you may read ahead


## Deeper Explanation
By now you know how to setup EzTiles as a virion and create
the most basic tile, so now you will find out how to add
functionality to your tile

Tile data is the most basic way to store your data in the tile
for retrieval and later use. Data is passed on as the second
argument of a `TileInfo` instance, as shown below

```php
$tileInfo = new TileInfo($pos, ["id" => "simpleTile"]);
```

This TileInfo class now stores an `"id"` key, which holds
a value of `"simpleTile"` in the tile.

Following that array format of `$key => $value`, you can
store your own data in the tile. It is recommended to keep
`"id" => "simpleTile"` as it is, because SimpleTile's tile
id is registered as `"simpleTile"`. The effects of removing
the id are unknown to me, but I personally suggest 
you to keep it as it is for safety :)

Also, the `$key` in every data piece must be a string, and
`$value` must be a variable of one of the types below:

`Integer, Boolean, String, Long, Short, Float, Double, Int Array, Bool Array`

Data cannot hold any instances or callables, because they cannot be saved
to the tile's NBT. Also, only specific array types are
allowed, such as an integer array or a bool array. Bool array
is eventually converted to a byte array full of integers
and saved as a string of that array. I personally did not
completely understand how it works, so I would recommend to
use an integer array instead of a boolean array.

The last 2 arguments of TileInfo are used to handle the
updating of tile from time to time. There is no specific
time for tile updating, but it is still often useful to
interact with the tile from time to time. 

The first argument is a boolean to schedule update or not,
if set to false then updating is impossible and the last
argument becomes useless, but if set to true then the last
argument is required.

```php
new TileInfo($pos, $data, true, $callable);
```

If you know PHP well, you might think that the last argument,
`$callable`, is a `callable`, but in fact it is a simple
string, which must be a static method's name. The static method
passed on will be executed on any scheduled update. It is
also important to note that the method must be IN the registrant
class.

Here is an example of how you would make a fully functional
`TileInfo` instance with saved data and an update scheduler

```php
new TileInfo(new Position(0, 0, 0), ["id" => "simpleTile", "randomInt" => 4], true, "staticUpdateMethod");
```

Now that you have created the tile and provided a method
string of `staticUpdateMethod`, you have to create it IN
your registrant class as a PUBLIC STATIC FUNCTION with SimpleTile
as its one and only required argument, and with a returnable
bool. I am uncertain, but I believe that the returned bool
will determine whether to continue updating the tile or not.

This is what it would look like:

```php
public static function staticUpdateMethod(SimpleTile $tile) : bool {
    /*In here, insert the code that should be executed 
    with the $tile variable upon every update that 
    the tile has*/
    //Note: don't forget to return true to continue updating, and return false to stop updating
}
```

For the last part, let's talk about how you can retrieve
and modify the data stored in your tile. As of now there
are only two methods allowing that, and they are pretty 
self-explanatory after you see them:

```php
public static function staticUpdateMethod(SimpleTile $tile) : bool {
    $id = $tile->getData("id", false);

    if($id == false){
        $tile->setData("id", "simpleTile");
    }

    return true;
}
```

The first part of the code above will retrieve the 
tile's `"id"` value every update, and if the value is 
not found it will return `false`, since that is the default
value. The second part of the code sets the tile's `"id"` 
to `"simpleTile"` if the value equals to `false`. The third
part of the code returns `true`, so the tile will continue
to get updates.

This is about it, hope this little guide helped you get
started :D

More tile possibilities & enhancements are planned in the
future, but as of now I will remain silent about them.

## Namespaces
```php
use DenielWorld\EzTiles\EzTiles; //Main file for registration
use DenielWorld\EzTiles\tile\SimpleTile; //The most basic and as of now the only custom tile that you can use
use DenielWorld\EzTiles\data\TileInfo; //Object that has to be passed on to SimpleTile as the second argument upon creation, it contains all data relevant to the SimpleTile
```