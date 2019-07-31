<?php
// Callback RiBot Server
// Source: https://github.com/Ridys/ribot
// Dmitry Ageykin (c) 2017
if (!isset($_REQUEST)) { 
  return; 
}
// подключение файла с токеном сообщества
require_once 'adm/token.php';
//Получаем и декодируем уведомление 
$data = json_decode(file_get_contents('php://input')); 

switch ($data->type) { 
  case 'confirmation': 
    echo $confirmation_token; 
    break; 

  case 'message_new': 
// получаем информацию о сообщении и юзере
    $user_id = $data->object->user_id; 
    $text = $data->object->body; 

    // проверка количества символов
      $symbols = strlen($text);
      if ($symbols >= 255) {
        $message = 'Прости, но создатель запретил мне отвечать на сообщения более 255 символов :-(';
        goto result_send;
      }

    // замена символов
      $points = array('.', 'ё', "'", '?', '!', ',', ')', '(', ':');
      $replace = array('', 'е', "", '', '', '', '', '', '');
      $textr = str_replace($points, $replace, $text);

    // действия с базой (общение)
    require_once 'adm/db.php';
    $random = mt_rand(1, 2);
    $sql = "SELECT `$random` FROM `answer` WHERE `text`='$textr'";
      if ($result = mysqli_query($link, $sql)) {
      $message = "Прости, я не знаю что ответить >((";
      /* выборка данных и помещение их в массив */
      while ($row = mysqli_fetch_row($result)) {
          $message = $row[0];
      }
      /* очищаем результирующий набор */
      mysqli_free_result($result);
      mysqli_close($link); }

result_send:
    $request_params = array( 
      'message' => $message, 
      'user_id' => $user_id, 
      'access_token' => $token, 
      'v' => '5.0' 
    ); 
$get_params = http_build_query($request_params); 
file_get_contents('https://api.vk.com/method/messages.send?'. $get_params); 

echo('ok'); 
break; 

} 

?> 