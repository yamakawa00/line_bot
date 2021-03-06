<?php

require_once __DIR__ . '/vendor/autoload.php';

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

$signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
    $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch (\LINE\LINEBot\Exception\InvalidSignatureException $e) {
    error_log("parseEventRequest failed. InvalidSignatureException => " . var_export($e, true));
} catch (\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
    error_log("parseEventRequest failed. UnknownEventTypeException => " . var_export($e, true));
} catch (\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
    error_log("parseEventRequest failed. UnknownMessageTypeException => " . var_export($e, true));
} catch (\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
    error_log("parseEventRequest failed. InvalidEventRequestException => " . var_export($e, true));
}

foreach ($events as $event) {
    $profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
    if (($event instanceof \LINE\LINEBot\Event\BeaconDetectionEvent)) {
//        error_log($event->getHwid());
        error_log('ビーコン');
        replyTextMessage($bot, $event->getReplyToken(), 'ビーコンイベント発火');
        exit;
    }

    if ($event instanceof \LINE\LINEBot\Event\FollowEvent) {
        error_log("友達追加");
        error_log($event->getReplyToken());

        $url = parse_url(getenv('DATABASE_URL'));
        $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
        $pdo = new PDO($dsn, $url['user'], $url['pass']);

        $sql = 'insert into public.user (user_line_id, name, comment, picture_url) values (:user_line_id, :name, :comment, :picture_url)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":user_line_id", $profile["userId"]);
        $stmt->bindValue(":name", $profile["displayName"]);
        $stmt->bindValue(":comment", $profile["statusMessage"]);
        $stmt->bindValue(":picture_url", $profile["pictureUrl"]);
        $flag = $stmt->execute();


        replyConfirmTemplate($bot,
            $event->getReplyToken(),
            "友達追加",
            "Webで詳しく見ますか？",
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
                "WebReseach", "https://" . $_SERVER["HTTP_HOST"] . "/get.php?USERID=" . $profile["userId"] . "&PIC=" . $profile["pictureUrl"]),
            new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder (
                "見ない", "ignore")
        );
    }

    if ($event instanceof \LINE\LINEBot\Event\UnfollowEvent) {
        error_log("友達解除");
    }

    // if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
    //     error_log('Non message event has come');
    //     continue;
    // }
    // if (!($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
    //     error_log('Non text message has come');
    //     continue;
    // }

//    $profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
//    // $message = $profile["displayName"] . "さん、ランダムでスタンプで返答します。";
//    $message2 = "http://codezine.jp/article/detail/9905";
////    $user_id = $profile["userId"];
////    $displayName = $profile["displayName"];
////    error_log($displayName);
//    // $user_info = array(
//    //     $profile["displayName"],
//    //     $profile["userId"],
//    //     $profile["pictureUrl"],
//    //     $profile["statusMessage"]
//    // );
//
//    foreach ($profile as $k => $v) {
//        error_log($k . ":" . $v);
//    }
//    error_log("update");
//
//    $url = parse_url(getenv('DATABASE_URL'));
//    $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
//    $pdo = new PDO($dsn, $url['user'], $url['pass']);
//
//    $sql = 'insert into public.user (user_line_id, name, comment, picture_url) values (:user_line_id, :name, :comment, :picture_url)';
//    $stmt = $pdo->prepare($sql);
//    $stmt->bindValue(":user_line_id", $profile["userId"]);
//    $stmt->bindValue(":name", $profile["displayName"]);
//    $stmt->bindValue(":comment", $profile["statusMessage"]);
//    $stmt->bindValue(":picture_url", $profile["pictureUrl"]);
//    $flag = $stmt->execute();
//
// if ($flag){
//    error_log('データの追加に成功しました');
// }else{
//    error_log('データの追加に失敗しました');
// }

// 返答するLINEスタンプをランダムで算出
    // $stkid = mt_rand(1, 17);

    // $bot->replyMessage($event->getReplyToken(),
    //  (new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder())
    //    ->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message))
    //    ->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message2))
    //    ->add(new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(1, $stkid))
    // );

    // replyTextMessage($bot, $event->getReplyToken(), "TextMessage");
//
//    replyImageMessage($bot, $event->getReplyToken(), "https://" . $_SERVER["HTTP_HOST"] . "/imgs/original.jpg", "https://" . $_SERVER["HTTP_HOST"] . "/imgs/preview.jpg");

//    if ($postback) {
//        switch ($pbMsg) {
//            case "1_1":
//                replyTextMessage($bot, $event->getReplyToken(), "正解");
//            case "1_2":
//            case "1_3":
//            case "1_4":
//                $bot->replyMessage($event->getReplyToken(),
//                    (new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder())
//                        ->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("不正解、キングダム全巻を買って復習しよう！"))
//                        ->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("https://www.amazon.co.jp/dp/B002DE793M/"))
//                );
//        }
//        continue;
//    }
//
//    replyButtonsTemplate($bot,
//        $event->getReplyToken(),
//        "キングダム クイズ",
//        "https://" . $_SERVER["HTTP_HOST"] . "/imgs/1.jpg",
//        "問題",
//        "秦の怪鳥の異名を持つ六大将軍といえば？",
//        new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
//            "王騎", "1_1"),
//        new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
//            "羌瘣", "1_2"),
//        new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
//            "嬴政", "1_3"),
//        new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
//            "信", "1_4")
//    );

//    $columnArray = array();
//    for($i = 0; $i < 5; $i++) {
//        $actionArray = array();
//        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder (
//            "ボタン" . $i . "-" . 1, "c-" . $i . "-" . 1));
//        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder (
//            "ボタン" . $i . "-" . 2, "c-" . $i . "-" . 2));
//        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder (
//            "ボタン" . $i . "-" . 3, "c-" . $i . "-" . 3));
//        $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder (
//            ($i + 1) . "日後の天気",
//            "晴れ",
//            "https://" . $_SERVER["HTTP_HOST"] .  "/imgs/template.jpg",
//            $actionArray
//        );
//        array_push($columnArray, $column);
//      }
    // replyCarouselTemplate($bot, $event->getReplyToken(),"今後の天気予報", $columnArray);

}

function replyTextMessage($bot, $replyToken, $text)
{
    $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
    if (!$response->isSucceeded()) {
        error_log('Failed!' . $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

function replyImageMessage($bot, $replyToken, $originalImageUrl, $previewImageUrl)
{
    $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalImageUrl, $previewImageUrl));
    if (!$response->isSucceeded()) {
        error_log('Failed!' . $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

function replyButtonsTemplate($bot, $replyToken, $alternativeText, $imageUrl, $title, $text, ...$actions)
{
    $actionArray = array();
    foreach ($actions as $value) {
        array_push($actionArray, $value);
    }
    $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
        $alternativeText,
        new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder ($title, $text, $imageUrl, $actionArray)
    );
    $response = $bot->replyMessage($replyToken, $builder);
    if (!$response->isSucceeded()) {
        error_log('Failed!' . $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

function replyCarouselTemplate($bot, $replyToken, $alternativeText, $columnArray)
{
    $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
        $alternativeText,
        new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder (
            $columnArray)
    );
    $response = $bot->replyMessage($replyToken, $builder);
    if (!$response->isSucceeded()) {
        error_log('Failed!' . $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

function replyConfirmTemplate($bot, $replyToken, $alternativeText, $text, ...$actions)
{
    $actionArray = array();
    foreach ($actions as $value) {
        array_push($actionArray, $value);
    }
    $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
        $alternativeText,
        new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder ($text, $actionArray)
    );
    $response = $bot->replyMessage($replyToken, $builder);
    if (!$response->isSucceeded()) {
        error_log('Failed!' . $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

?>
