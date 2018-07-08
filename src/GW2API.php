<?php
/**
 * Class GW2API
 *
 * @link https://api.guildwars2.com/v2
 * @link https://wiki.guildwars2.com/wiki/API:Main
 *
 * GW2 does not support authentication (anymore) but the API still works like a regular OAUth API, so...
 *
 * @filesource   GW2API.php
 * @created      05.07.2018
 * @package      chillerlan\GW2DB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB;

use chillerlan\MagicAPI\{
	ApiClientInterface, ApiClientTrait
};
use chillerlan\OAuth\Core\{
	OAuth2Provider, AccessToken, ProviderException
};

/**
 * @method \chillerlan\HTTP\HTTPResponseInterface account(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountAchievements(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountBank(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountDungeons(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountDyes(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountFinishers(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountGliders(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountHomeCats(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountHomeNodes(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountInventory(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountMailcarriers(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountMasteries(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountMasteryPoints(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountMaterials(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountMinis(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountOutfits(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountPvpHeroes(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountRaids(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountRecipes(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountSkins(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountTitles(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface accountWallet(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface achievements(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface achievementsCategories(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface achievementsCategoriesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface achievementsDaily()
 * @method \chillerlan\HTTP\HTTPResponseInterface achievementsDailyTomorrow()
 * @method \chillerlan\HTTP\HTTPResponseInterface achievementsGroups(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface achievementsGroupsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface achievementsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface backstoryAnswers(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface backstoryAnswersId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface backstoryQuestions(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface backstoryQuestionsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface build()
 * @method \chillerlan\HTTP\HTTPResponseInterface cats()
 * @method \chillerlan\HTTP\HTTPResponseInterface catsId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface characters(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersId($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdBackstory($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdCore($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdCrafting($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdEquipment($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdHeropoints($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdInventory($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdRecipes($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdSab($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdSkills($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdSpecializations($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface charactersIdTraining($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface colors(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface colorsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceDelivery(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceExchange()
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceExchangeCoins(array $params = ['quantity'])
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceExchangeGems(array $params = ['quantity'])
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceListings()
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceListingsId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface commercePrices()
 * @method \chillerlan\HTTP\HTTPResponseInterface commercePricesId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceTransactions(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceTransactionsCurrent()
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceTransactionsCurrentBuys()
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceTransactionsCurrentSells()
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceTransactionsHistory()
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceTransactionsHistoryBuys()
 * @method \chillerlan\HTTP\HTTPResponseInterface commerceTransactionsHistorySells()
 * @method \chillerlan\HTTP\HTTPResponseInterface continents(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface continentsContinentId($continent_id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface continentsContinentIdFloors($continent_id)
 * @method \chillerlan\HTTP\HTTPResponseInterface continentsContinentIdFloorsFloorId($continent_id, $floor_id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface continentsContinentIdFloorsFloorIdRegions($continent_id, $floor_id)
 * @method \chillerlan\HTTP\HTTPResponseInterface continentsContinentIdFloorsFloorIdRegionsRegionId($continent_id, $floor_id, $region_id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface continentsContinentIdFloorsFloorIdRegionsRegionIdMaps($continent_id, $floor_id, $region_id)
 * @method \chillerlan\HTTP\HTTPResponseInterface continentsContinentIdFloorsFloorIdRegionsRegionIdMapsMapId($continent_id, $floor_id, $region_id, $map_id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface currencies(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface currenciesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface dungeons(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface dungeonsId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface emblem()
 * @method \chillerlan\HTTP\HTTPResponseInterface emblemBackgrounds()
 * @method \chillerlan\HTTP\HTTPResponseInterface emblemBackgroundsId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface emblemForegrounds()
 * @method \chillerlan\HTTP\HTTPResponseInterface emblemForegroundsId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface files()
 * @method \chillerlan\HTTP\HTTPResponseInterface filesId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface finishers(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface finishersId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface gliders(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface glidersId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildId($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildIdLog($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildIdMembers($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildIdRanks($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildIdStash($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildIdStorage($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildIdTeams($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildIdTreasury($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildIdUpgrades($id, array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildPermissions(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildPermissionsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildSearch()
 * @method \chillerlan\HTTP\HTTPResponseInterface guildUpgrades(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface guildUpgradesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface items(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface itemsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface itemstats(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface itemstatsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface legends()
 * @method \chillerlan\HTTP\HTTPResponseInterface legendsId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface mailcarriers(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface mailcarriersId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface maps(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface mapsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface masteries(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface masteriesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface materials(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface materialsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface minis(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface minisId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface nodes()
 * @method \chillerlan\HTTP\HTTPResponseInterface nodesId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface outfits(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface outfitsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pets(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface petsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface professions(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface professionsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvp()
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpAmulets(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpAmuletsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpGames(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpHeroes(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpHeroesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpRacesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpRanks(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpRanksId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpSeasons(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpSeasonsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpSeasonsIdLeaderboards($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpSeasonsIdLeaderboardsBoardIdRegionId($id, $board, $region)
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpStandings(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface pvpStats(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface quaggans()
 * @method \chillerlan\HTTP\HTTPResponseInterface quaggansId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface races(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface raids(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface raidsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface recipes()
 * @method \chillerlan\HTTP\HTTPResponseInterface recipesId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface recipesSearch()
 * @method \chillerlan\HTTP\HTTPResponseInterface skills(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface skillsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface skins(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface skinsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface specializations(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface specializationsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface stories(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface storiesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface storiesSeasons(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface storiesSeasonsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface titles(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface titlesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface tokeninfo(array $params = ['access_token'])
 * @method \chillerlan\HTTP\HTTPResponseInterface traits(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface traitsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface worlds(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface worldsId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwAbilities(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwAbilitiesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatches()
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesOverview()
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesOverviewId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesScores()
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesScoresId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesStats()
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesStatsId($id)
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesStatsIdGuildsGuildId($id, $guild_id)
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesStatsIdTeamsTeamIdTopKdr($id, $team)
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwMatchesStatsIdTeamsTeamIdTopKills($id, $team)
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwObjectives(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwObjectivesId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwRanks(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwRanksId($id, array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwUpgrades(array $params = ['lang'])
 * @method \chillerlan\HTTP\HTTPResponseInterface wvwUpgradesId($id, array $params = ['lang'])
 */
class GW2API extends OAuth2Provider implements ApiClientInterface{
	use ApiClientTrait;

	public const SCOPE_ACCOUNT     = 'account';
	public const SCOPE_INVENTORIES = 'inventories';
	public const SCOPE_CHARACTERS  = 'characters';
	public const SCOPE_TRADINGPOST = 'tradingpost';
	public const SCOPE_WALLET      = 'wallet';
	public const SCOPE_UNLOCKS     = 'unlocks';
	public const SCOPE_PVP         = 'pvp';
	public const SCOPE_BUILDS      = 'builds';
	public const SCOPE_PROGRESSION = 'progression';
	public const SCOPE_GUILDS      = 'guilds';

	protected const AUTH_ERRMSG = 'The Guild Wars 2 API doesn\'t support OAuth authentication anymore';

	protected $apiURL        = 'https://api.guildwars2.com/v2';
	protected $authURL       = 'https://account.arena.net/applications/create';
	protected $userRevokeURL = 'https://account.arena.net/applications';
	protected $endpointMap    = GW2APIEndpoints::class;

	/**
	 * @inheritdoc
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function getAuthURL(array $params = null):string{
		throw new ProviderException($this::AUTH_ERRMSG);
	}

	/**
	 * @inheritdoc
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function getAccessToken(string $code, string $state = null):AccessToken{
		throw new ProviderException($this::AUTH_ERRMSG);
	}

	/**
	 * @param string $access_token
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function storeGW2Token(string $access_token):AccessToken{

		if(!preg_match('/^[a-f\d\-]{72}$/i', $access_token)){
			throw new ProviderException('invalid token');
		}

		// manual http request here to verify the token and avoid accessing the storage before firing an API request
		$tokeninfo = $this->httpGET($this->apiURL.'/tokeninfo', ['access_token' => $access_token])->json;

		if(isset($tokeninfo->id) && strpos($access_token, $tokeninfo->id) === 0){

			$token = new AccessToken([
				'provider'     => $this->serviceName,
				'accessToken'       => $access_token,
				'accessTokenSecret' => substr($access_token, 36, 36), // the actual token
				'expires'           => AccessToken::EOL_NEVER_EXPIRES,
				'extraParams'       => [
					'token_type' => 'Bearer',
					'id'         => $tokeninfo->id,
					'name'       => $tokeninfo->name,
					'scope'      => implode($this->scopesDelimiter, $tokeninfo->permissions),
				],
			]);

			$this->storage->storeAccessToken($this->serviceName, $token);

			return $token;
		}

		throw new ProviderException('unverified token'); // @codeCoverageIgnore
	}

}
