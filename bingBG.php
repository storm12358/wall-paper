<?php

$wall_paper_folder = "/Users/kyou/Pictures/background/";
$backup_folder = "/Users/kyou/Pictures/backups/";

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "http://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=3&mkt=en-US");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($curl);
curl_close($curl);
$json_data = json_decode($result, true);
$images = $json_data['images'];

$exist_images = scandir($wall_paper_folder);
$exist_images = array_diff($exist_images, ['.', '..']);

// 下载图片
foreach ($images as $item) {
    $item_url = $item['url'];
    $image_name = end(explode("/", $item_url));
    $formatted_name = $item['startdate'] . "_" . $image_name;
    // 防止重复下载
    if (in_array($formatted_name, $exist_images)) {
        continue;
    }

    ob_start();
    readfile($item_url);
    $img_content = ob_get_contents();
    ob_end_clean();

    $fo = fopen($wall_paper_folder . $formatted_name, 'a');
    fwrite($fo, $img_content);
    fclose($fo);
}
// 移动七天前图片
foreach ($exist_images as $image_name) {
    $file = $wall_paper_folder . $image_name;
    $create_time = filectime($file);
    $size = filesize($file);
    // 小于 10K 的删掉
    if ($size < 10000) {
        unlink($file);
        continue;
    }
    // 移动
    if ((time() - $create_time) > 7*24*3600) {
        rename($file, $backup_folder . $image_name);
    }
}
