<?php 
	
	/*
	*  Helper class for making http requests
	*/
	class HttpHelper
	{
		
		private $proxyServer = "139.59.118.0:3128";
		/*
		*  Methods to imlement Singleton pattern
		*/
		public static function getHelper()
		{
			$instance = null;
			if($instance == null)
			{
				$instance = new HttpHelper();
			}  
			return $instance;
		}

    	public function getHtml($url)
    	{

        	$ch = curl_init($url);

            $cookie_file = "cookies.txt";

			//curl_setopt($ch, CURLOPT_HEADER, 1);
        	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			if($this->proxyServer)
            {
            	curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
				curl_setopt($ch, CURLOPT_PROXY, $this->proxyServer);
            }

            curl_setopt( $ch, CURLOPT_COOKIESESSION, true );
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Host: vprognoze.ru',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'DNT: 1',
                'Accept-Encoding: gzip, deflate, br',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Cookie: autotimezone=2; rerf=AAAAAFmlr2NvIxGfAxL4Ag==; PHPSESSID=n3bd3btl30uj23n80gjfk4d7r3; login_user_token=264dc439c1154741c5b2f0850780341b; ipp_uid2=QCuB9rk8h1E5toVJ/yU24OnDo/JYzwGFnohihlA==; ipp_uid1=1504030563248; ipp_key=1504031017035/wcAcuS+8WCm1/GNW7pgKwQ==; __utma=187128303.1396406998.1504030568.1504030568.1504030568.1; __utmb=187128303.5.10.1504030568; __utmc=187128303; __utmz=187128303.1504030568.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); _ym_uid=1504031079635062103; _ym_visorc_5916940=w; _ym_isad=2'
            ));   



        	$html = curl_exec($ch);

            $html = gzdecode($html);

            /*var_dump($html);
            exit;*/
			
        	return $html;
    	}


        public function divideUrls($urls, $numberOfThreads)
        {
            $result = array();

            $amountOfElements = count($urls);

            if($amountOfElements < $numberOfThreads)
            {
                $numberOfThreads = $amountOfElements;
            }

            // делит массив url на части в соотвествии с количеством потоков установленных пользователем
            for ($i = 0; $i < $amountOfElements; $i = $i + $numberOfThreads) {
                $urlsList = array();
                for ($j = $i; $j < $i + $numberOfThreads; $j++) {
                    if (isset($urls[$j]) && !(is_null($urls[$j]))) {
                        $urlsList[] = $urls[$j];
                    }
                }

                $result[] = $urlsList;
            }

            return $result;
        }

        public function fetchUrlsFromMultiResult($multiResult)
        {
            $result = array();

            foreach($multiResult as $singleHtml)
            {
                $result[] = $singleHtml["url"];
            }

            return $result;
        }

        public function multiRequest($urlsList)
        {
            $curlDescriptors = $this->getCurlDescriptors(count($urlsList), $urlsList);
            $curlMulti = $this->createCurlMulti($curlDescriptors);
            $htmlArray = $this->executeCurlMultiAndGetHtmlArray($curlMulti, $curlDescriptors);

            return $htmlArray;
        }

    	public function getCurlDescriptors($amount, $urls)
    	{
        	$curlDescriptors = array();

        	for ($i = 0; $i < $amount; $i++) {
            	$curlDescriptors[$i] = $this->getCurlDescriptor($urls[$i]);
        	}

        	return $curlDescriptors;
   		}

    	public function createCurlMulti($descriptors)
    	{
        	$mh = curl_multi_init();

        	foreach ($descriptors as $singleDescriptor) {
                curl_multi_add_handle($mh, $singleDescriptor);
        	}

        	return $mh;
    	}	

        public function executeCurlMultiAndGetHtmlArray($mh, $curlArr)
        {
        	$htmlArray = array();

		
        	//запускаем дескрипторы
        	do {
            	curl_multi_exec($mh,$running);
        	} while($running > 0);

        	$counter = 0;
			$node_count = count($curlArr);

			for($i = 0; $i < $node_count; $i++)
			{
				$htmlArray[$counter]         = array();
            	$htmlArray[$counter]["url"]  = curl_getinfo($curlArr[$i], CURLINFO_EFFECTIVE_URL);
            	$htmlArray[$counter]["html"] = curl_multi_getcontent( $curlArr[$i]  );
				$counter++;
            	curl_close($curlArr[$i]);
			}

        	curl_multi_close($mh);

        	return $htmlArray;
    	}

    	public function getCurlDescriptor($url)
    	{
        	$ch = curl_init($url);

        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        	return $ch;
    	}

        public function getHttpCode($curlDescr)
        {
            return curl_getinfo($curlDescr, CURLINFO_HTTP_CODE);
        }

        public function parseCookies($responseString)
        {
            preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $html, $matches);
            $cookies = array();
        
            foreach($matches[1] as $item) {
                parse_str($item, $cookie);
                $cookies = array_merge($cookies, $cookie);
            }
        
            return $cookies;
        }

    	/*
    	* Constructor
    	*/
		private function __construct()
		{

		}
	}

?>