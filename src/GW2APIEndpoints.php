<?php
/**
 * Class GW2APIEndpoints (auto created)
 *
 * @link https://api.guildwars2.com/v2
 *
 * @filesource   GW2APIEndpoints.php
 * @created      08.07.2018
 * @package      chillerlan\GW2DB
 * @license      MIT
 */

namespace chillerlan\GW2DB;

use chillerlan\MagicAPI\EndpointMap;

class GW2APIEndpoints extends EndpointMap{

	protected $account = [
		'path'          => '/account',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountAchievements = [
		'path'          => '/account/achievements',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountBank = [
		'path'          => '/account/bank',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountDungeons = [
		'path'          => '/account/dungeons',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountDyes = [
		'path'          => '/account/dyes',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountFinishers = [
		'path'          => '/account/finishers',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountGliders = [
		'path'          => '/account/gliders',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountHomeCats = [
		'path'          => '/account/home/cats',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountHomeNodes = [
		'path'          => '/account/home/nodes',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountInventory = [
		'path'          => '/account/inventory',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountMailcarriers = [
		'path'          => '/account/mailcarriers',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountMasteries = [
		'path'          => '/account/masteries',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountMasteryPoints = [
		'path'          => '/account/mastery/points',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountMaterials = [
		'path'          => '/account/materials',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountMinis = [
		'path'          => '/account/minis',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountOutfits = [
		'path'          => '/account/outfits',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountPvpHeroes = [
		'path'          => '/account/pvp/heroes',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountRaids = [
		'path'          => '/account/raids',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountRecipes = [
		'path'          => '/account/recipes',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountSkins = [
		'path'          => '/account/skins',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountTitles = [
		'path'          => '/account/titles',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $accountWallet = [
		'path'          => '/account/wallet',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $achievements = [
		'path'          => '/achievements',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $achievementsCategories = [
		'path'          => '/achievements/categories',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $achievementsCategoriesId = [
		'path'          => '/achievements/categories/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $achievementsDaily = [
		'path'          => '/achievements/daily',
		'query'         => [],
		'path_elements' => [],
	];

	protected $achievementsDailyTomorrow = [
		'path'          => '/achievements/daily/tomorrow',
		'query'         => [],
		'path_elements' => [],
	];

	protected $achievementsGroups = [
		'path'          => '/achievements/groups',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $achievementsGroupsId = [
		'path'          => '/achievements/groups/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $achievementsId = [
		'path'          => '/achievements/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $backstoryAnswers = [
		'path'          => '/backstory/answers',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $backstoryAnswersId = [
		'path'          => '/backstory/answers/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $backstoryQuestions = [
		'path'          => '/backstory/questions',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $backstoryQuestionsId = [
		'path'          => '/backstory/questions/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $build = [
		'path'          => '/build',
		'query'         => [],
		'path_elements' => [],
	];

	protected $cats = [
		'path'          => '/cats',
		'query'         => [],
		'path_elements' => [],
	];

	protected $catsId = [
		'path'          => '/cats/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $characters = [
		'path'          => '/characters',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $charactersId = [
		'path'          => '/characters/%1$s',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdBackstory = [
		'path'          => '/characters/%1$s/backstory',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdCore = [
		'path'          => '/characters/%1$s/core',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdCrafting = [
		'path'          => '/characters/%1$s/crafting',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdEquipment = [
		'path'          => '/characters/%1$s/equipment',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdHeropoints = [
		'path'          => '/characters/%1$s/heropoints',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdInventory = [
		'path'          => '/characters/%1$s/inventory',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdRecipes = [
		'path'          => '/characters/%1$s/recipes',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdSab = [
		'path'          => '/characters/%1$s/sab',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdSkills = [
		'path'          => '/characters/%1$s/skills',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdSpecializations = [
		'path'          => '/characters/%1$s/specializations',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $charactersIdTraining = [
		'path'          => '/characters/%1$s/training',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $colors = [
		'path'          => '/colors',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $colorsId = [
		'path'          => '/colors/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $commerceDelivery = [
		'path'          => '/commerce/delivery',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $commerceExchange = [
		'path'          => '/commerce/exchange',
		'query'         => [],
		'path_elements' => [],
	];

	protected $commerceExchangeCoins = [
		'path'          => '/commerce/exchange/coins',
		'query'         => ['quantity'],
		'path_elements' => [],
	];

	protected $commerceExchangeGems = [
		'path'          => '/commerce/exchange/gems',
		'query'         => ['quantity'],
		'path_elements' => [],
	];

	protected $commerceListings = [
		'path'          => '/commerce/listings',
		'query'         => [],
		'path_elements' => [],
	];

	protected $commerceListingsId = [
		'path'          => '/commerce/listings/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $commercePrices = [
		'path'          => '/commerce/prices',
		'query'         => [],
		'path_elements' => [],
	];

	protected $commercePricesId = [
		'path'          => '/commerce/prices/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $commerceTransactions = [
		'path'          => '/commerce/transactions',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $commerceTransactionsCurrent = [
		'path'          => '/commerce/transactions/current',
		'query'         => [],
		'path_elements' => [],
	];

	protected $commerceTransactionsCurrentBuys = [
		'path'          => '/commerce/transactions/current/buys',
		'query'         => [],
		'path_elements' => [],
	];

	protected $commerceTransactionsCurrentSells = [
		'path'          => '/commerce/transactions/current/sells',
		'query'         => [],
		'path_elements' => [],
	];

	protected $commerceTransactionsHistory = [
		'path'          => '/commerce/transactions/history',
		'query'         => [],
		'path_elements' => [],
	];

	protected $commerceTransactionsHistoryBuys = [
		'path'          => '/commerce/transactions/history/buys',
		'query'         => [],
		'path_elements' => [],
	];

	protected $commerceTransactionsHistorySells = [
		'path'          => '/commerce/transactions/history/sells',
		'query'         => [],
		'path_elements' => [],
	];

	protected $continents = [
		'path'          => '/continents',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $continentsContinentId = [
		'path'          => '/continents/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['continent_id'],
	];

	protected $continentsContinentIdFloors = [
		'path'          => '/continents/%1$s/floors',
		'query'         => [],
		'path_elements' => ['continent_id'],
	];

	protected $continentsContinentIdFloorsFloorId = [
		'path'          => '/continents/%1$s/floors/%2$s',
		'query'         => ['lang'],
		'path_elements' => ['continent_id', 'floor_id'],
	];

	protected $continentsContinentIdFloorsFloorIdRegions = [
		'path'          => '/continents/%1$s/floors/%2$s/regions',
		'query'         => [],
		'path_elements' => ['continent_id', 'floor_id'],
	];

	protected $continentsContinentIdFloorsFloorIdRegionsRegionId = [
		'path'          => '/continents/%1$s/floors/%2$s/regions/%3$s',
		'query'         => ['lang'],
		'path_elements' => ['continent_id', 'floor_id', 'region_id'],
	];

	protected $continentsContinentIdFloorsFloorIdRegionsRegionIdMaps = [
		'path'          => '/continents/%1$s/floors/%2$s/regions/%3$s/maps',
		'query'         => [],
		'path_elements' => ['continent_id', 'floor_id', 'region_id'],
	];

	protected $continentsContinentIdFloorsFloorIdRegionsRegionIdMapsMapId = [
		'path'          => '/continents/%1$s/floors/%2$s/regions/%3$s/maps/%4$s',
		'query'         => ['lang'],
		'path_elements' => ['continent_id', 'floor_id', 'region_id', 'map_id'],
	];

	protected $currencies = [
		'path'          => '/currencies',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $currenciesId = [
		'path'          => '/currencies/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $dungeons = [
		'path'          => '/dungeons',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $dungeonsId = [
		'path'          => '/dungeons/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $emblem = [
		'path'          => '/emblem',
		'query'         => [],
		'path_elements' => [],
	];

	protected $emblemBackgrounds = [
		'path'          => '/emblem/backgrounds',
		'query'         => [],
		'path_elements' => [],
	];

	protected $emblemBackgroundsId = [
		'path'          => '/emblem/backgrounds/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $emblemForegrounds = [
		'path'          => '/emblem/foregrounds',
		'query'         => [],
		'path_elements' => [],
	];

	protected $emblemForegroundsId = [
		'path'          => '/emblem/foregrounds/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $files = [
		'path'          => '/files',
		'query'         => [],
		'path_elements' => [],
	];

	protected $filesId = [
		'path'          => '/files/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $finishers = [
		'path'          => '/finishers',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $finishersId = [
		'path'          => '/finishers/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $gliders = [
		'path'          => '/gliders',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $glidersId = [
		'path'          => '/gliders/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $guildId = [
		'path'          => '/guild/%1$s',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $guildIdLog = [
		'path'          => '/guild/%1$s/log',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $guildIdMembers = [
		'path'          => '/guild/%1$s/members',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $guildIdRanks = [
		'path'          => '/guild/%1$s/ranks',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $guildIdStash = [
		'path'          => '/guild/%1$s/stash',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $guildIdStorage = [
		'path'          => '/guild/%1$s/storage',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $guildIdTeams = [
		'path'          => '/guild/%1$s/teams',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $guildIdTreasury = [
		'path'          => '/guild/%1$s/treasury',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $guildIdUpgrades = [
		'path'          => '/guild/%1$s/upgrades',
		'query'         => ['access_token'],
		'path_elements' => ['id'],
	];

	protected $guildPermissions = [
		'path'          => '/guild/permissions',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $guildPermissionsId = [
		'path'          => '/guild/permissions/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $guildSearch = [
		'path'          => '/guild/search',
		'query'         => [],
		'path_elements' => [],
	];

	protected $guildUpgrades = [
		'path'          => '/guild/upgrades',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $guildUpgradesId = [
		'path'          => '/guild/upgrades/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $items = [
		'path'          => '/items',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $itemsId = [
		'path'          => '/items/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $itemstats = [
		'path'          => '/itemstats',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $itemstatsId = [
		'path'          => '/itemstats/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $legends = [
		'path'          => '/legends',
		'query'         => [],
		'path_elements' => [],
	];

	protected $legendsId = [
		'path'          => '/legends/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $mailcarriers = [
		'path'          => '/mailcarriers',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $mailcarriersId = [
		'path'          => '/mailcarriers/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $maps = [
		'path'          => '/maps',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $mapsId = [
		'path'          => '/maps/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $masteries = [
		'path'          => '/masteries',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $masteriesId = [
		'path'          => '/masteries/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $materials = [
		'path'          => '/materials',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $materialsId = [
		'path'          => '/materials/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $minis = [
		'path'          => '/minis',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $minisId = [
		'path'          => '/minis/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $nodes = [
		'path'          => '/nodes',
		'query'         => [],
		'path_elements' => [],
	];

	protected $nodesId = [
		'path'          => '/nodes/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $outfits = [
		'path'          => '/outfits',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $outfitsId = [
		'path'          => '/outfits/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $pets = [
		'path'          => '/pets',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $petsId = [
		'path'          => '/pets/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $professions = [
		'path'          => '/professions',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $professionsId = [
		'path'          => '/professions/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $pvp = [
		'path'          => '/pvp',
		'query'         => [],
		'path_elements' => [],
	];

	protected $pvpAmulets = [
		'path'          => '/pvp/amulets',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $pvpAmuletsId = [
		'path'          => '/pvp/amulets/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $pvpGames = [
		'path'          => '/pvp/games',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $pvpHeroes = [
		'path'          => '/pvp/heroes',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $pvpHeroesId = [
		'path'          => '/pvp/heroes/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $pvpRacesId = [
		'path'          => '/pvp/races/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $pvpRanks = [
		'path'          => '/pvp/ranks',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $pvpRanksId = [
		'path'          => '/pvp/ranks/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $pvpSeasons = [
		'path'          => '/pvp/seasons',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $pvpSeasonsId = [
		'path'          => '/pvp/seasons/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $pvpSeasonsIdLeaderboards = [
		'path'          => '/pvp/seasons/%1$s/leaderboards',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $pvpSeasonsIdLeaderboardsBoardIdRegionId = [
		'path'          => '/pvp/seasons/%1$s/leaderboards/%2$s/%3$s',
		'query'         => [],
		'path_elements' => ['id', 'board', 'region'],
	];

	protected $pvpStandings = [
		'path'          => '/pvp/standings',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $pvpStats = [
		'path'          => '/pvp/stats',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $quaggans = [
		'path'          => '/quaggans',
		'query'         => [],
		'path_elements' => [],
	];

	protected $quaggansId = [
		'path'          => '/quaggans/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $races = [
		'path'          => '/races',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $raids = [
		'path'          => '/raids',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $raidsId = [
		'path'          => '/raids/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $recipes = [
		'path'          => '/recipes',
		'query'         => [],
		'path_elements' => [],
	];

	protected $recipesId = [
		'path'          => '/recipes/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $recipesSearch = [
		'path'          => '/recipes/search',
		'query'         => [],
		'path_elements' => [],
	];

	protected $skills = [
		'path'          => '/skills',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $skillsId = [
		'path'          => '/skills/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $skins = [
		'path'          => '/skins',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $skinsId = [
		'path'          => '/skins/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $specializations = [
		'path'          => '/specializations',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $specializationsId = [
		'path'          => '/specializations/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $stories = [
		'path'          => '/stories',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $storiesId = [
		'path'          => '/stories/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $storiesSeasons = [
		'path'          => '/stories/seasons',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $storiesSeasonsId = [
		'path'          => '/stories/seasons/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $titles = [
		'path'          => '/titles',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $titlesId = [
		'path'          => '/titles/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $tokeninfo = [
		'path'          => '/tokeninfo',
		'query'         => ['access_token'],
		'path_elements' => [],
	];

	protected $traits = [
		'path'          => '/traits',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $traitsId = [
		'path'          => '/traits/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $worlds = [
		'path'          => '/worlds',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $worldsId = [
		'path'          => '/worlds/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $wvwAbilities = [
		'path'          => '/wvw/abilities',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $wvwAbilitiesId = [
		'path'          => '/wvw/abilities/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $wvwMatches = [
		'path'          => '/wvw/matches',
		'query'         => [],
		'path_elements' => [],
	];

	protected $wvwMatchesId = [
		'path'          => '/wvw/matches/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $wvwMatchesOverview = [
		'path'          => '/wvw/matches/overview',
		'query'         => [],
		'path_elements' => [],
	];

	protected $wvwMatchesOverviewId = [
		'path'          => '/wvw/matches/overview/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $wvwMatchesScores = [
		'path'          => '/wvw/matches/scores',
		'query'         => [],
		'path_elements' => [],
	];

	protected $wvwMatchesScoresId = [
		'path'          => '/wvw/matches/scores/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $wvwMatchesStats = [
		'path'          => '/wvw/matches/stats',
		'query'         => [],
		'path_elements' => [],
	];

	protected $wvwMatchesStatsId = [
		'path'          => '/wvw/matches/stats/%1$s',
		'query'         => [],
		'path_elements' => ['id'],
	];

	protected $wvwMatchesStatsIdGuildsGuildId = [
		'path'          => '/wvw/matches/stats/%1$s/guilds/%2$s',
		'query'         => [],
		'path_elements' => ['id', 'guild_id'],
	];

	protected $wvwMatchesStatsIdTeamsTeamIdTopKdr = [
		'path'          => '/wvw/matches/stats/%1$s/teams/%2$s/top/kdr',
		'query'         => [],
		'path_elements' => ['id', 'team'],
	];

	protected $wvwMatchesStatsIdTeamsTeamIdTopKills = [
		'path'          => '/wvw/matches/stats/%1$s/teams/%2$s/top/kills',
		'query'         => [],
		'path_elements' => ['id', 'team'],
	];

	protected $wvwObjectives = [
		'path'          => '/wvw/objectives',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $wvwObjectivesId = [
		'path'          => '/wvw/objectives/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $wvwRanks = [
		'path'          => '/wvw/ranks',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $wvwRanksId = [
		'path'          => '/wvw/ranks/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

	protected $wvwUpgrades = [
		'path'          => '/wvw/upgrades',
		'query'         => ['lang'],
		'path_elements' => [],
	];

	protected $wvwUpgradesId = [
		'path'          => '/wvw/upgrades/%1$s',
		'query'         => ['lang'],
		'path_elements' => ['id'],
	];

}
