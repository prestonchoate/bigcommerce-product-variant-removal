<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$accessToken = $_ENV['ACCESS_TOKEN'] ?? null;
$apiPath = $_ENV['API_PATH'] ?? null;
$clientId = $_ENV['CLIENT_ID'] ?? null;
$clientSecret = $_ENV['CLIENT_SECRET'] ?? null;

// This chunk is to run a single product ID
$productId = 8832;
$productOptions = GetJson($apiPath . 'catalog/products/' . $productId . '/options');
$optionsToRemove = [];
foreach ($productOptions->data as $productOption) {
    $optionIds = [];
    foreach ($productOption->option_values as $optionValue) {
        $optionIds[$optionValue->id] = ['label' => $optionValue->label, 'option_label' => $productOption->display_name];
    }
    $optionsToRemove[$productOption->id] = $optionIds;
}
$variants = GetJson($apiPath . 'catalog/products/' . $productId . '/variants');
foreach ($variants->data as $variant) {
    $selectedOptions = $variant->option_values;
    foreach ($selectedOptions as $selectedOption) {
        if (isset($optionsToRemove[$selectedOption->option_id])) {
            if (array_key_exists($selectedOption->id, $optionsToRemove[$selectedOption->option_id])) {
                unset($optionsToRemove[$selectedOption->option_id][$selectedOption->id]);
            }
        }
    }
}
foreach ($optionsToRemove as $index => $optionToRemove) {
    $url = $apiPath . 'catalog/products/' . $productId . '/options/' . $index;
    foreach ($optionToRemove as $option) {
        echo 'Removing value ' . $option['label'] . ' for option ' . $option['option_label'] . PHP_EOL;
        //DeleteRequest($url . '/values/' . $optionId);
    }
}

// This chunk is to run for all products on the store

//$productResponse = GetJson($apiPath . 'catalog/products');
//$pages = $productResponse->meta->pagination->total_pages ?? 0;
//echo 'There are ' . $pages . ' pages of products' . PHP_EOL;
//$processedPages = 1;
//while ($processedPages <= $pages) {
//    echo PHP_EOL;
//    echo '**************' . PHP_EOL;
//    echo 'Processing page ' . $processedPages . PHP_EOL;
//    echo '**************';
//    echo PHP_EOL . PHP_EOL;
//    $productResponse = GetJson($apiPath . 'catalog/products?page=' . $processedPages);
//    $products = $productResponse->data;
//    foreach ($products as $product) {
//        echo 'Processing product ' . $product->sku . PHP_EOL;
//        $productId = $product->id;
//        $productOptions = GetJson($apiPath . 'catalog/products/' . $productId . '/options');
//        $optionsToRemove = [];
//        foreach ($productOptions->data as $productOption) {
//            $optionIds = [];
//            foreach ($productOption->option_values as $optionValue) {
//                $optionIds[] = ['id' => $optionValue->id, 'label' => $optionValue->label, 'option_label' => $productOption->display_name];
//            }
//            $optionsToRemove[$productOption->id] = $optionIds;
//        }
//        $variants = GetJson($apiPath . 'catalog/products/' . $productId . '/variants');
//        foreach ($variants->data as $variant) {
//            $selectedOptions = $variant->option_values;
//            foreach ($selectedOptions as $selectedOption) {
//                if (isset($optionsToRemove[$selectedOption->option_id])) {
//                    $pos = array_search($selectedOption->id, $optionsToRemove[$selectedOption->option_id]);
//                    if ($pos) {
//                        unset($optionsToRemove[$selectedOption->option_id][$pos]);
//                    }
//                }
//            }
//        }
//        foreach ($optionsToRemove as $index => $optionToRemove) {
//            $url = $apiPath . 'catalog/products/' . $productId . '/options/' . $index;
//            foreach ($optionToRemove as $option) {
//                echo 'Removing value ' . $option['label'] . ' for option ' . $option['option_label'] . PHP_EOL;
//                DeleteRequest($url . '/values/' . $option['id']);
//            }
//        }
//        echo PHP_EOL;
//    }
//    $processedPages++;
//}


function GetJson($api_url)
{
    global $accessToken;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Length: 0'));
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Auth-Token: ' . $accessToken]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);

    return json_decode($response);
}

function DeleteRequest($api_url)
{
    global $accessToken;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Length: 0'));
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Auth-Token: ' . $accessToken]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    echo 'Sending DELETE request to ' . $api_url . PHP_EOL;
    curl_exec($ch);
}