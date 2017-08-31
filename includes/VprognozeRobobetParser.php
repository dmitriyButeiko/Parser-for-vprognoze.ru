<?php 

	require_once dirname(__FILE__) . "/HttpHelper.php";
	require_once dirname(__FILE__) . "/SimpleHtmlDom.php";

	class VprognozeRobobetParser
	{
		private $matchesUrl = "https://vprognoze.ru/robobet/";
		private $mainUrl = "https://vprognoze.ru";
		private $httpHelper;

		public static function getInstance()
		{
			$instance = null;
			if($instance == null)
			{
				$instance = new VprognozeRobobetParser();
			}  

			return $instance;
		}

		public function getMatches()
		{
			$matches = array();

			//get two pages of matches $matchesPagesHtmlArray

			$mainMatchesPageHtml = $this->httpHelper->getHtml($this->matchesUrl);

			$parsedMatches = $this->parseMatches($mainMatchesPageHtml);
			$currentDate = $this->getCurrentDate($mainMatchesPageHtml);
			$matches[$currentDate] = $parsedMatches;

			// getting yesterday matches

			$yeasterdayMatchesUrl = $this->mainUrl . $this->getYesterdayMatchesUrl($mainMatchesPageHtml);
			$yeasterdayMatchesHtml = $this->httpHelper->getHtml($yeasterdayMatchesUrl);
			$parsedMatchesYesterday = $this->parseMatches($yeasterdayMatchesHtml);
			$currentDate = $this->getCurrentDate($yeasterdayMatchesHtml);

			$matches[$currentDate] = $parsedMatchesYesterday;

			return $matches;
		}

		private function getCurrentDate($html)
		{	
			//var_dump($html);
			$html = str_get_html($html);

			$dateSpan = $html->find("div.news_boxing div.navigation span", 0);

			if($dateSpan)
			{
				$currentDate = $dateSpan->innertext;
			}
			else
			{
				$currentDate = "Unavailable";
			}

			return $currentDate;
		}

		private function getYesterdayMatchesUrl($html)
		{
			$html = str_get_html($html);

			$yesterdayMatchesUrl = $html->find("div.news_boxing div.navigation a", 0)->href;

			return $yesterdayMatchesUrl;
		}

		private function parseMatches($html)
		{
			$matches = array();

			$html = str_get_html($html);

			$itemCounter = 0;

			foreach($html->find(".robot-table > tbody > tr") as $singleListItem)
			{

				$matches[$itemCounter] = array();


				$timeBlock = $singleListItem->find("td", 0);

				if($timeBlock == null)
				{
					continue;
				}

				$time = $timeBlock->innertext;


				$matches[$itemCounter]["time"] = $time;

				$matches[$itemCounter]["commands"] = $singleListItem->find("td", 1)->find("a", 0)->innertext;

				$matches[$itemCounter]["1win"] = $singleListItem->find("td", 2)->innertext;
				$matches[$itemCounter]["x"] = $singleListItem->find("td", 3)->innertext;
				$matches[$itemCounter]["2win"] = $singleListItem->find("td", 4)->innertext;
				$matches[$itemCounter]["bet"] = $singleListItem->find("td", 5)->innertext;
				$matches[$itemCounter]["kf1"] = $singleListItem->find("td", 6)->find("a span", 0)->innertext;
				$matches[$itemCounter]["kfx"] = $singleListItem->find("td", 7)->find("a span", 0)->innertext;
				$matches[$itemCounter]["kf2"] = $singleListItem->find("td", 8)->find("a span", 0)->innertext;
				$matches[$itemCounter]["result"] = $singleListItem->find("td", 9)->innertext;

				$itemCounter++;
			}


			return $matches;
		}

		public function __construct()
		{
			$this->httpHelper = HttpHelper::getHelper();
		}
	}
?>