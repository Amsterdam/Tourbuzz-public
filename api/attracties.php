<?php

$topAttracties = [
	"Rijksmuseum",
	"Van Gogh Museum",
	"Stedelijk Museum",
	"Foam Fotografiemuseum Amsterdam",
	"Hermitage Amsterdam",
	"Joods Historisch Museum",
	"Hortus Botanicus Amsterdam - De Hortus",
	"Natura Artis Magistra",
	"Tropenmuseum",
	"Het Scheepvaartmuseum Amsterdam",
	"Science Center NEMO",
	"Rembrandthuis",
	"Amsterdam Museum",
	"Madame Tussauds Amsterdam",
	"Anne Frank Huis",
	"EYE Filmmuseum",
	"Gassan Diamonds",
	"Coster Diamonds",
	"Heineken Experience",
	"Amsterdam Centraal Station"
];

$sourceUrl = "http://www.amsterdamopendata.nl/files/Attracties.json";

$cacheFilepath = "cache/attracties.json";
if (is_file($cacheFilepath) && time() - filemtime($cacheFilepath) < 24 * 3600) {
	$fileContents = file_get_contents($cacheFilepath);
} else {
	$fileContents = file_get_contents($sourceUrl);
	file_put_contents($cacheFilepath, $fileContents);
}
$jsonData = json_decode($fileContents);

$uriParts = array_values(array_filter(explode("/", $_SERVER["REQUEST_URI"])));

$result = [
	"_uri" => $uriParts,
	"_bron" => $sourceUrl
];

$attractieId = !empty($uriParts[1]) ? $uriParts[1] : null;

foreach ($jsonData as $data) {
	if ($data->location->city !== "AMSTERDAM") continue;
	if (!in_array($data->title, $topAttracties)) continue;
	if ($attractieId && $attractieId !== $data->trcid) continue;
	$attractie = (object) [
		"id" => $data->trcid,
		"naam" => $data->title,
		"adres" => $data->location->adress,
		"coordinaten" => [
			"lat" => floatval(str_replace(",", ".", $data->location->latitude)),
			"lng" => floatval(str_replace(",", ".", $data->location->longitude))
		],
		"media" => $data->media,
		"top" => in_array($data->title, $topAttracties),
		"_origineel" => $data
	];
	if ($attractieId) {
		$result["attractie"] = $attractie;
		break;
	} else {
		$result["attracties"][] = $attractie;
	}
}

header("Content-type: application/json");
echo json_encode($result);
