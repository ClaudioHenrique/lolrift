<?php
	
	/*
	* @author: Cláudio Henrique
	* @email: claudiohenriquedev@gmail.com
	* @about: Essa API recupera informações de uma partida que esta sendo jogando em LoL, obtendo informações de todos os jogadores
	* @return: Toda a aplicação retorna as informações utilizando JSON
	* @date: 17/11/2014
	*/
	

	include_once ('../vendor/autoload.php');
	include_once('../vendor/unirest-php-1.2.1/lib/Unirest.php');

	\Slim\Slim::registerAutoloader();

	$GLOBALS['key'] = 'bbb704d4-1628-41cc-a596-6359cb50ad30'; //Key do riotgames.com

	$app = new \Slim\Slim();

	$app->get('/', function () {
		echo 'Hello! Welcome to our API (: ';
	});

	$app->get('/contato', function () {
		echo 'Hi, our e-mail: hello@lolrift.com.br';
	});

	$app->get('/getSummonersIds/:name/', 'getSummonersIds');
	$app->get('/getSummonersNames/:name/', 'getSummonersName');
	$app->post('/getLeague/', 'getLeague');
	$app->get('/getChampion/:name/', 'getChampion');
	$app->post('/getMasteriesPoints/', 'getMasteriesPoints');

	$app->get('/getLeague/',function() {

		echo 'Olá, envie uma requisição utilizando o METODO POST passando como parametro o SummonersIds dos jogadores no seguinte formato : <br><br>';
		echo '<pre>';
		print_r(
			"
					[
					    {
					        'id': '001'
					    },
					    {
					        'id': '002'
					    }
					]
			"
			);
		echo '</pre>';

	});

	//Recuper o nome de todos os jogadores da partida atual
	function getSummonersName($namePlayer){
		$names = array();

		$response = Unirest::get("https://spectator-league-of-legends-v1.p.mashape.com/lol/br/v1/spectator/by-name/$namePlayer",
				array(
					"X-Mashape-Key" => "WDdDYHuqYLmshznB011K61QDTA4Ip1MHzOIjsnS4BgktTVXiub"
				)
		);

		$json = json_decode(json_encode($response->body),true);	

		if(!isset($json['data']['error'])){
			if(!empty($json['data']['game'])){

					$i = 0;

					foreach($json['data']['game']['teamOne'] as $teamOne){
						$names['teamOne'][$i] =  $teamOne['summonerName'];
						$i++;
					}


					$i = 0;
					foreach($json['data']['game']['teamTwo'] as $teamTwo){
						$names['teamTwo'][$i] = $teamTwo['summonerName'];
						$i++;
					}

					echo '<pre>';
					print_r($names);
			}
		}else{
			echo json_encode(
				array(
					"status" => 404	
				)
			);
		}

	}

	//Informo o nome do Jogador (Player) e recupero os SummonersIds de todos os Players da partida
	function getSummonersIds($namePlayer){
		
		$ids = array();

		$response = Unirest::get("https://spectator-league-of-legends-v1.p.mashape.com/lol/br/v1/spectator/by-name/$namePlayer",
				array(
					"X-Mashape-Key" => "WDdDYHuqYLmshznB011K61QDTA4Ip1MHzOIjsnS4BgktTVXiub"
				)
		);

		$json = json_decode(json_encode($response->body),true);	

		echo '<pre>';
		print_r($json);
		echo '</pre>';

		if(!isset($json['data']['error'])){
			if(!empty($json['data']['game'])){

					$i = 0;

					foreach($json['data']['game']['teamOne'] as $teamOne){
						$ids['teamOne'][$i] =  $teamOne['summonerId'];
						$i++;
					}


					$i = 0;
					foreach($json['data']['game']['teamTwo'] as $teamTwo){
						$ids['teamTwo'][$i] = $teamTwo['summonerId'];
						$i++;
					}

					echo '<pre>';
					print_r($ids);
			}
		}else{
			echo json_encode(
				array(
					"status" => 404	
				)
			);
		}

		return $ids; //Retorno os summonors ids dos players dos dois times
	
	}


	function getChampion($namePlayer){

		$internalName = array();
		$champions = array();



		$response = Unirest::get("https://spectator-league-of-legends-v1.p.mashape.com/lol/br/v1/spectator/by-name/$namePlayer",
				array(
					"X-Mashape-Key" => "WDdDYHuqYLmshznB011K61QDTA4Ip1MHzOIjsnS4BgktTVXiub"
				)
		);

		$json = json_decode(json_encode($response->body),true);	

		//echo '<pre>';
		//print_r($json);

		if(!isset($json['data']['error'])){

			$i = 0;

			foreach($json['data']['game']['teamTwo'] as $teamTwo){
				$internalName[$i] =  $teamTwo['summonerInternalName'];
				$i++;
			}

			foreach($json['data']['game']['teamOne'] as $teamOne){
				$internalName[$i] = $teamOne['summonerInternalName'];
				$i++;
			}

			$i = 0;


			for($i = 0; $i < sizeof($json['data']['game']['playerChampionSelections']); $i++){
		     	for($j = 0; $j < sizeof($json['data']['game']['playerChampionSelections']); $j++){
		     		if(strcmp($internalName[$i], $json['data']['game']['playerChampionSelections'][$j]['summonerInternalName']) == 0) {
		     			$champions[$i] = array(
		     				"summonerInternalName" => $json['data']['game']['playerChampionSelections'][$j]['summonerInternalName'],
		     				"spell2Id" => $json['data']['game']['playerChampionSelections'][$j]['spell2Id'],
		     				"spell1Id" => $json['data']['game']['playerChampionSelections'][$j]['spell1Id'],
		     				"championId" => $json['data']['game']['playerChampionSelections'][$j]['championId']
		     			);
		     		}
		     	}
			}

			echo '<pre>';
			print_r($champions);

		}


	}

	function getLeague(){
		//Recebe a requisição com os SummonersIds de todos os jogadores da partida
		
		/*
			Estrutura da Requisição:

			[
			    {
			        "id": "001"
			    },
			    {
			        "id": "002"
			    }
			]
		*/

		global $app;
		$request = $app->request();
		$data = json_decode($request->getBody(),true);


		// Variavel onde as informações dos jogadres iram ficar armazenadas
		$leagues = array();

		//Variavel com a key da API da riotgames
		$key = $GLOBALS['key'];
		

		/*
			Faz com que os SummonersIds sejam separados por ponto e virgula
		*/

		$ids = "";

		for($i = 0; $i < sizeof($data); $i++){
			if($i == sizeof($data) - 1) {
				$ids .= $data[$i]['id'];
			}else{
				$ids .= $data[$i]['id'].",";
			}
		}

		$response = Unirest::get("https://br.api.pvp.net/api/lol/br/v2.5/league/by-summoner/$ids?api_key=$key");

		if(!empty($response)){
			$json = json_decode(json_encode($response->body),true);	
			$x = 0;

			for($i = 0; $i < sizeof($data); $i++) {

				//Aqui eu busco as principais informações de todos os jogadores
				foreach($json[$data[$i]['id']][0]['entries'] as $key => $value){
				      if($value['playerOrTeamId'] == $data[$i]['id']){

				      		$leagues[$x] = array (
				      				'playerOrTeamId' => $value['playerOrTeamId'],
				      				'playerOrTeamName' => $value['playerOrTeamName'],
				      				'division' => $value['division'],
				      				'leaguePoints' => $value['leaguePoints'],
				      				'wins' => $value['wins'],
				      				'isHotStreak' => $value['isHotStreak'],
				      				'isVeteran' => $value['isVeteran'],
				      				'isFreshBlood' => $value['isFreshBlood'],
				      				'isInactive' => $value['isInactive']
				      		);

				      		$x++;
				      }
				}
			}
			print_r($leagues);
		}

	}


	function getMasteriesPoints(){
		//Materies 
		$mt = array();

		//Informações do rank;
		$rk = array();
		$info = array();

		//Variavel global da Key da riotgames API
		$key = $GLOBALS['key'];
		
		//Recebo a Requisição
		global $app;
		$request = $app->request();
		$data = json_decode($request->getBody(),true);

		$ids = "";

		for($i = 0; $i < sizeof($data); $i++){
			if($i == sizeof($data) - 1) {
				$ids .= $data[$i]['id'];
			}else{
				$ids .= $data[$i]['id'].",";
			}
		}

		//Variaveis de controle
		$z = 0;
		$x = 0;

		
		echo '<pre>';
		preg_match_all('~\'[^\']++\'|\([^)]++\)|[^,]++~', $ids,$result); // Separo todos os ids para busca
		print_r($result);


		$response = Unirest::get("https://br.api.pvp.net/api/lol/br/v1.4/summoner/$ids/masteries/?api_key=$key");
		$json = json_decode(json_encode($response->body),true);	


		for($v = 0; $v < sizeof($result[0]); $v++){

			//ID Do player
			$id = $result[0][$v];
			$idPlay = $result[0][$v];


		foreach($json[$id]['pages'] as $masteries){

			if($masteries['current'] == 1){
				$mt[$z] = $masteries['masteries'];
				$z++;

				//Recupero informações statics para fazer o calculo das informações
				$fp = file_get_contents('static/masteries.json',"r");

				//Inicializo as variaveis
				$defense = 0;
				$utility = 0;
				$offense = 0;
				
				//Transformo as informações em um array
				$staticMasteries = json_decode($fp,true);

				//Posicao Atual do Array
				$atual = $z - 1;

				for ($i=0; $i < sizeof($mt[$atual]); $i++) { 


					$id = $mt[$atual][$i]['id'];
					$rank = $mt[$atual][$i]['rank'];

					for ($j = 0; $j < sizeof($staticMasteries['tree']['Defense']); $j++) { 
						for($w = 0; $w < sizeof($staticMasteries['tree']['Defense'][$j]['masteryTreeItems']); $w++){
							$idMasteries = $staticMasteries['tree']['Defense'][$j]['masteryTreeItems'][$w]['masteryId'];
							if($idMasteries == $id){
								$defense = $defense + $rank;
							}
						}
					}

					for ($j = 0; $j < sizeof($staticMasteries['tree']['Offense']); $j++) { 
						for($w = 0; $w < sizeof($staticMasteries['tree']['Offense'][$j]['masteryTreeItems']); $w++){
							$idMasteries = $staticMasteries['tree']['Offense'][$j]['masteryTreeItems'][$w]['masteryId'];
							if($idMasteries == $id){
								$offense = $offense + $rank;
							}
						}
					}

					for ($j = 0; $j < sizeof($staticMasteries['tree']['Utility']); $j++) { 
						for($w = 0; $w < sizeof($staticMasteries['tree']['Utility'][$j]['masteryTreeItems']); $w++){
							$idMasteries = $staticMasteries['tree']['Utility'][$j]['masteryTreeItems'][$w]['masteryId'];
							if($idMasteries == $id){
								$utility = $utility + $rank;
							}
						}
					}

					echo sizeof($mt[$atual]) - 1;

					if($i == sizeof($mt[$atual]) - 1 ){

						/*
						$info[$x] = array(
							"name" => $json[$idPlay]['pages'][0]['name'],
							"id" => $idPlay,
							"defense" => $defense,
							"utility" => $utility,
							"offense" => $offense
						);
						$x++;
						*/

						echo sizeof($mt[$atual]);
					}
					
					
				}	
				    
			}else{

			}
		}
		
	}
		echo '<pre>';
		print_r($mt);
		
	}

	$app->run();
?>