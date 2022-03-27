<?php
/******************************************************************
 librairie d'émulation des fonctions spécifiques à l'eedomus
http://doc.eedomus.com/view/Scripts#Scripts_.22Objets_connect.C3.A9s.22

script cree par twitter:@Havok pour la eedomus
27/03/22 : Fonctions emulées crées par Havok
           getArg
           getValue
           httpQuery
           jsonToXML
           loadVariable
           saveVariable
           sdk_get_input() //a vérifier
           sdk_json_decode
           xpath
27/03/22 : Espace de travail pour rajout des fonctions d'émulation developpées par Dommarion et Merguez07
****************************************************************/

/*** remplacez ***/
require("config.php");
/*** par ***/
//$api_user = "xxxxxxx";
//$api_secret = "xxxxxxx";

/*Exécute une requête HTTP/HTTPS et retourne son résultat sous forme de chaine de caractère.*/
function httpQuery($url, $action = 'GET'/*GET,POST,PUT,DELETE*/, $post = NULL, $oauth_token = NULL, $headers = NULL, $use_cookies = false, $ignore_errors = false, &$info = null) {

  $curl = curl_init(); //Première étape, initialiser une nouvelle session cURL.
  curl_setopt($curl, CURLOPT_URL, $url); //Il va par exemple falloir lui fournir l'url de la page à récupérer.

  if ($action == 'GET') {
    curl_setopt($curl, CURLOPT_HTTPGET, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
  } elseif ($action == 'POST') {
    curl_setopt($curl, CURLOPT_POST, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
  }
  if ($headers) {curl_setopt ($curl, CURLOPT_HTTPHEADER, $headers); }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Cette option permet d'indiquer que nous voulons recevoir le résultat du transfert au lieu de l'afficher.

  $return = curl_exec($curl); //Il suffit ensuite d'exécuter la requête
  $info = curl_getinfo($curl); //recupération des infos curl
  curl_close($curl);
  return $return;
}

function sdk_json_decode($json) {
  return json_decode($json,true);
}

function getArg($var, $mandatory = true, $default = ' ') {
  $return = '';
  if (isset($_GET[$var])) $return = $_GET[$var];
  return $return;
}

function saveVariable($variable_name, $variable_content) {
  return setcookie($variable_name, $variable_content, time()+3600*24*7);  /* expire dans 7 jours */
}

function loadVariable($variable_name) {
  return $_COOKIE[$variable_name];
}

function jsonToXML($json)
{
  // function definition to convert array to xml
  function array_to_xml( $data, &$xml_data ) {
      foreach( $data as $key => $value ) {
          if( is_numeric($key) ){
              $key = 'item'.$key; //dealing with <0/>..<n/> issues
          }
          if( is_array($value) ) {
              $subnode = $xml_data->addChild($key);
              array_to_xml($value, $subnode);
          } else {
              $xml_data->addChild("$key",htmlspecialchars("$value"));
          }
       }
  }
  //convert json to array
  $arr = json_decode($json, true);
  // creating object of SimpleXMLElement
  $xml_data = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
  // function call to convert array to xml
  array_to_xml($arr,$xml_data);
  return $xml_data->asXML();
}

function getValue($periph_id /*Code API*/, $value_text = false)
{
  /* Retourne un tableau contenant la valeur d'un périphérique via son code API.
Le tableau est de type array(["full_name"] => 'my device', ["value"] => xx, ["value_type"] => float, ["change"] => 'AAAA-MM-JJ HH:MM:SS', ["pending_action"] => NULL, ["unit"] => 'xx', ["icon"] => 'xx')
Si $value_text est à true, le tableau contiendra également ["value_text"]=> xx, qui correspond à la description de la valeur (ex. "On") */
  global $api_user, $api_secret;

  $periphCaract = httpQuery('https://api.eedomus.com/get?action=periph.caract&periph_id='.$periph_id.'&api_user='.$api_user.'&api_secret='.$api_secret.'&show_config=1','GET');
  $periphCaract = json_decode($periphCaract,true);

  $periphValue['full_name'] = $periphCaract['body']['name'];
  $periphValue['value'] = $periphCaract['body']['last_value'];
  $periphValue['value_type'] = $periphCaract['body']['value_type'];
  $periphValue['change'] = $periphCaract['body']['last_value_change'];
  $periphValue['unit'] = $periphCaract['body']['unit'];
  if ($value_text) { $periphValue['value_text'] = $periphCaract['body']['last_value_text']; }

  return $periphValue;
}

function xpath($xml, $path)
{
  $xmlSource = new SimpleXMLElement($xml);
  $result = $xmlSource->xpath($path);
  while(list( , $node) = each($result)) {
    break;
  }

  return $node;
}

function sdk_get_input() //a vérifier
  {
    //Renvoi le contenu de php://input
    // get the raw POST data
    $rawData = file_get_contents("php://input");

    // this returns null if not valid json
    return json_decode($rawData);
  }

 ?>
