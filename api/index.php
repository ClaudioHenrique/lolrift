<?php
	
	/*
	* @author: Cláudio Henrique
	* @email: claudiohenriquedev@gmail.com
	* @about: Essa API recupera informações de uma partida que esta sendo jogando em LoL, obtendo informações de todos os jogadores
	* @return: Toda a aplicação retorna as informações utilizando JSON
	* @date: 17/11/2014 - 15/12/2014
	* @Message: Apenas os métodos getLeague() e getMasteriesPoints() é que recebem requisições POST, consultar o método para verificar a forma de efetuar essas requisições.
	*/

	include_once ('../vendor/autoload.php');
	include_once('../vendor/unirest-php-1.2.1/lib/Unirest.php');
    include_once('lib/simple_html_dom.php');

	\Slim\Slim::registerAutoloader();

	$GLOBALS['key'] = 'a6cdd4b0-ac93-44d9-aada-7cf8c93d38ae'; //Key do riotgames.com

	$app = new \Slim\Slim();

    $app->post('/getMasteriesPoints/', 'getMasteriesPoints');
    $app->post('/getLeague/', 'getLeague');
    $app->post('/getChampion/', 'getChampion');
    $app->post('/getNameChampion/','getNameChampion');

    $app->get('/', function () {
		echo 'Hello! Welcome to our API (: <br><br> LolRift Team: <br> Cláudio Henrique (Developer) <br> Antero Junior (Gamer)';
	});

	$app->get('/contato', function () {
		echo 'Hi, our e-mail: hello@lolrift.com.br';
	});

    $app->get('/getRanked/:id/:name/','getRanked');
	$app->get('/getSummonersIds/:name/', 'getSummonersIds');
	$app->get('/getSummonersNames/:name/', 'getSummonersName');
	$app->get('/getLeague/',function() {

		echo 'Olá, envie uma requisição utilizando o METODO POST passando como parametro o SummonersIds dos jogadores no seguinte formato : <br><br>';
		echo "<pre>";

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

	});
    $app->get('/getRankedStats/:idPlayer/', 'getRankedStats');
    $app->get('/getWins/:id/','getWins');


	//Recuper o nome de todos os jogadores da partida atual
	function getSummonersName($namePlayer){
		$names = array();

        /**
         * Removo os espaços em branco caso exista no Sumonnor name do cara
         */

        $nomeFormatado = '';

        $namePlayer = explode(' ',$namePlayer);

        for($i = 0; $i < sizeof($namePlayer); $i++){
            $nomeFormatado .= $namePlayer[$i];
        }


		$response = Unirest::get("https://spectator-league-of-legends-v1.p.mashape.com/lol/br/v1/spectator/by-name/$nomeFormatado",
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

			}
		}else{
			echo json_encode(
				array(
					"status" => 404	
				)
			);
		}

        return $names;

	}

	//Informo o nome do Jogador (Player) e recupero os SummonersIds de todos os Players da partida
	function getSummonersIds($namePlayer){
		
		$ids = array();

        /**
         * Removo os espaços em branco caso exista no Sumonnor name do cara
         */

        $nomeFormatado = '';

        $namePlayer = explode(' ',$namePlayer);

        for($i = 0; $i < sizeof($namePlayer); $i++){
            $nomeFormatado .= $namePlayer[$i];
        }

        $nomes = getSummonersName($nomeFormatado);
        echo '<pre>';
        print_r($nomes);


		$response = Unirest::get("https://spectator-league-of-legends-v1.p.mashape.com/lol/br/v1/spectator/by-name/$nomeFormatado",
				array(
					"X-Mashape-Key" => "WDdDYHuqYLmshznB011K61QDTA4Ip1MHzOIjsnS4BgktTVXiub"
				)
		);

		$json = json_decode(json_encode($response->body),true);

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

    //Inforomo o nome do Player e recupero o seu melhor campeão
	function getChampion(){

		$internalName = array();
		$champions = array();
        $nomeFormatado = '';
        $formatado = '';
        $todosNomes = '';


        global $app;
        $request = $app->request();
        $data = json_decode($request->getBody(),true);

        for($i = 0; $i < sizeof($data); $i++){
            if($i == sizeof($data) - 1) {
                $nomeFormatado .= $data[$i]['nome'];
            }else{
                $nomeFormatado .= $data[$i]['nome'].",";
            }
        }

        $novo = explode(',',$nomeFormatado);

        for($i = 0; $i < sizeof($novo); $i++){
            $x = explode(' ',$novo[$i]);
            if(count($x) > 1) {
               for($j = 0; $j < sizeof($x); $j++){
                   $formatado .= $x[$j];
               }
            }else{
                $formatado .= $novo[$i];
            }
            $formatado = $formatado . ',';
        }

        $formatado = explode(',',$formatado);

        for($i = 0; $i < sizeof($formatado)-1; $i++){

           $name = $formatado[$i];

            $response = Unirest::get("https://spectator-league-of-legends-v1.p.mashape.com/lol/br/v1/spectator/by-name/$name",
                    array(
                        "X-Mashape-Key" => "WDdDYHuqYLmshznB011K61QDTA4Ip1MHzOIjsnS4BgktTVXiub"
                    )
            );

            $json = json_decode(json_encode($response->body),true);

            if(!isset($json['data']['error'])){

                $i = 0;

                foreach($json['data']['game']['teamOne'] as $teamOne){
                    $internalName['teamone'][$i] =  $teamOne['summonerInternalName'];
                    $i++;
                }

                $i = 0;

                foreach($json['data']['game']['teamTwo'] as $teamTwo){
                    $internalName['teamtwo'][$i] =  $teamTwo['summonerInternalName'];
                    $i++;
                }


                for($i = 0; $i < sizeof($internalName['teamone']); $i++){
                    for($j = 0; $j < sizeof($json['data']['game']['playerChampionSelections']); $j++){
                        if(strcmp($internalName['teamone'][$i], $json['data']['game']['playerChampionSelections'][$j]['summonerInternalName']) == 0){
                            $champions['teamOne'][$i] = array(
                                "summonerInternalName" => $json['data']['game']['playerChampionSelections'][$j]['summonerInternalName'],
                                "spell2Id" => $json['data']['game']['playerChampionSelections'][$j]['spell2Id'],
                                "spell1Id" => $json['data']['game']['playerChampionSelections'][$j]['spell1Id'],
                                "championId" => $json['data']['game']['playerChampionSelections'][$j]['championId']
                            );
                        }
                    }
                }


                for($i = 0; $i < sizeof($internalName['teamtwo']); $i++){
                    for($j = 0; $j < sizeof($json['data']['game']['playerChampionSelections']); $j++){
                        if(strcmp($internalName['teamtwo'][$i], $json['data']['game']['playerChampionSelections'][$j]['summonerInternalName']) == 0){
                            $champions['teamTwo'][$i] = array(
                                "summonerInternalName" => $json['data']['game']['playerChampionSelections'][$j]['summonerInternalName'],
                                "spell2Id" => $json['data']['game']['playerChampionSelections'][$j]['spell2Id'],
                                "spell1Id" => $json['data']['game']['playerChampionSelections'][$j]['spell1Id'],
                                "championId" => $json['data']['game']['playerChampionSelections'][$j]['championId']
                            );
                        }
                    }
                }


            }else{
                echo json_encode(array(
                    "status" => "404",
                    "mensagem" => "Eastamos com problema na utilização da API"
                ));

            }
        }

        echo '<pre>';
        print_r($champions);
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
                      $tier = $json[$data[$i]['id']][0]['tier'];

				      if($value['playerOrTeamId'] == $data[$i]['id']){

				      		$leagues[$x] = array (
                                    'tier' => $tier,
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


		preg_match_all('~\'[^\']++\'|\([^)]++\)|[^,]++~', $ids,$result); // Separo todos os ids para busca


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


					if($i == sizeof($mt[$atual]) - 1 ){
						$info[$x] = array(
							"name" => $json[$idPlay]['pages'][0]['name'],
							"id" => $idPlay,
							"defense" => $defense,
							"utility" => $utility,
							"offense" => $offense
						);
						$x++;

					}
					
					
				}	
				    
			}else{

			}
		}
		
	}
		echo '<pre>';
        print_r($info);
	}

     /*
     * Informo o id do Campeão e me retorna o Nome do campeão!
     * */

    function getNameChampion(){
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


            $champions = file_get_contents('static/champions.json');
            $champ = json_decode($champions,true);

            preg_match_all('~\'[^\']++\'|\([^)]++\)|[^,]++~', $ids,$result); // Separo todos os ids para a busca

            echo '<pre>';
            print_r($result);

            foreach($champ['data'] as $key => $value){
                echo $champ['data'][$key]['name'].'<br>';
            }
    }

    //Verifico se o player esta rankeado, informando o nome do seu melhor campeão e o nome do jogador
    function getRanked($idChampion, $namePlayer){
        //Status do rankeamento
        $ranked = false;
        //Caso o jogador esteja listado no ranking, essa váriavel armazena a sua posição! :)
        $math = 0;

        //URL responsavel por retornar as informações do Rank do Brasil
        $html = file_get_html("http://www.lolskill.net/top?filterChampion=$idChampion&filterRealm=BR");
        //Magia negra responsavel por recuperar a quantidade de páginas da URL
        preg_match_all('/<div class=\"pagination\">(.*?)<\/div>/s',$html,$matches);
        $x = strip_tags($matches[0][0]);
        $position = strpos($x,"N"); //Recupero a quantidade de páginas

        $nicks[0] = getNickPlayers($html);

        #Responsavel por acessar as páginas internas da pesquisa
        for($i = 1; $i <= $position; $i++){
            if($i != 1){
                $size = sizeof($nicks);
                $html = file_get_html("http://www.lolskill.net/top?filterChampion=$idChampion&filterRealm=BR&p=$i");
                $nicks[$i-1] = getNickPlayers($html);
            }
        }

        #Verifico se o nome do player existe no Ranking
        for($i = 0; $i < sizeof($nicks); $i++){
            for($j = 0; $j < sizeof($nicks[$i]); $j++){
                for($w = 0; $w < sizeof($nicks[$i][$j]); $w++){
                    $n = $nicks[$i][$j][$w];
                    if(strcmp(strip_tags($n),$namePlayer) == 0){
                        $ranked = true;
                        if($i == 0) {
                            $math =  $w+1;
                        }else{
                            $math = (($i * 30) + $w) + 1;
                        }
                    }
                }
            }
        }

        //Verifico de o player foi rankeado
        if($ranked){
            echo json_encode(
                array(
                    "ranked" => true,
                    "position" => $math
                )
            );
        }else{
            echo json_encode(
                array(
                    "ranked" => false
                )
            );
        }


    }

    function getRankedStats($idPlayer){

        $totalGames = 0;
        $totalWins = 0;
        $totalLoss = 0;
        $totalKill = 0;
        $totalDeath = 0;
        $totalAssist = 0;


        $key = $GLOBALS['key'];
        if(!empty($idPlayer) && !empty($idChamp)){
            $html = file_get_html("https://br.api.pvp.net/api/lol/br/v1.3/stats/by-summoner/$idPlayer/ranked?season=SEASON4&api_key=$key");
            $array = json_decode($html,true);
            //echo '<pre>';
            //print_r($array);

            for($i = 0; $i < sizeof($array['champions']); $i++){

                $totalGames = $totalGames + $array['champions'][$i]['stats']['totalSessionsPlayed']; //Quantidade de partidas jogadas
                $totalWins = $totalWins + $array['champions'][$i]['stats']['totalSessionsWon']; //Quantidade de vitorias
                $totalLoss = $totalLoss + $array['champions'][$i]['stats']['totalSessionsLost']; //Quantidade de derrotas

                $totalKill = $totalKill + $array['champions'][$i]['stats']['totalChampionKills']; //Quantidade de Homicidios
                $totalDeath = $totalDeath + $array['champions'][$i]['stats']['totalDeathsPerSession']; //Quantidade de Mortas

                $totalAssist = $totalAssist + $array['champions'][$i]['stats']['totalAssists']; //Quantidade de assistencias.
            }


            $mediaKill = number_format($totalKill/$totalGames,1);
            $mediaDeath = number_format($totalDeath/$totalGames,1);
            $mediaAssist = number_format($totalAssist/$totalGames,1);

            echo json_encode(
                array(
                    "games" => $totalGames,
                    "wins" => $totalWins,
                    "loss" => $totalLoss,
                    "kill" => $totalKill,
                    "death" => $totalDeath,
                    "assist" => $totalAssist,
                    "K" => $mediaKill,
                    "D" => $mediaDeath,
                    "A" => $mediaAssist
                )
            );

        }else{
            echo json_encode(
                array(
                    "status" => 404,
                    "mensagem" => "Forneeca todos os parametros, id do player e id do campeão"
                )
            );
        }

    }

    function getRankedChamp($idPlayer, $idChamp){

    }


    function getWins($idPlayer){
        $key = $GLOBALS['key'];
        $normalWins = 0;
        $soloWins = 0;
        $teamWins = 0;
        $teamLosses = 0;

        if(!empty($idPlayer)) {
            $html = file_get_html("https://br.api.pvp.net/api/lol/br/v1.3/stats/by-summoner/$idPlayer/summary?season=SEASON4&api_key=$key");
            $json = json_decode($html, true);
            for ($i = 0; $i < sizeof($json['playerStatSummaries']); $i++) {
                if ($json['playerStatSummaries'][$i]['playerStatSummaryType'] == 'Unranked') {
                    $normalWins = $normalWins + $json['playerStatSummaries'][$i]['wins'];
                }

                if ($json['playerStatSummaries'][$i]['playerStatSummaryType'] == 'RankedSolo5x5') {
                    $soloWins = $soloWins +  $json['playerStatSummaries'][$i]['wins'];
                }

                if ($json['playerStatSummaries'][$i]['playerStatSummaryType'] == 'RankedTeam5x5') {
                    $teamWins = $teamWins + $json['playerStatSummaries'][$i]['wins'];
                    $teamLosses = $teamLosses + $json['playerStatSummaries'][$i]['losses'];
                }
            }

            echo json_encode(
                array(
                    "normal" => $normalWins,
                    "solo" => $soloWins,
                    "team" => $teamWins,
                    "losers" => $teamLosses
                )
            );


        }
    }

    function getNickPlayers($html){
        //Magia negra responsavel por recuperar o nick dos campeões para efetuar verificação.
        preg_match_all('/<td class=\"summoner left\">(.*?)<\/td>/s',$html,$matches);
        return $matches;
    }

	$app->run();
?>