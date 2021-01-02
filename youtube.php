<?php

class youtube
{
    public $baseUrl = "https://m.youtube.com";

    private function getData($url = "", $post = false, $val = "")
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $val);
        }
        curl_setopt($ch, CURLOPT_REFERER, "googlebot");
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1");

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }


    public function search($q = "")
    {
        $getData = $this->getData("/results?q={$q}&hl=tr");

        preg_match_all('#var ytInitialData =(.*?)</script>#si', $getData, $matches, PREG_SET_ORDER);
        $data = preg_replace_callback('#\\\x([0-9A-F]{2})#si', function ($item) {
            return chr(intval($item[1], 16));
        }, $matches[0][1]);
        $data = str_replace(["\\\\", " '{", "';"], ["", "{", ""], $data);
        $data = json_decode($data, true);

        $item = [];
        if (
            $data
            && $data['contents']
            && $data['contents']['sectionListRenderer']
            && $data['contents']['sectionListRenderer']['contents']
            && count($data['contents']['sectionListRenderer']['contents']) > 0
            && $data['contents']['sectionListRenderer']['contents'][1]['itemSectionRenderer']
            && count($data['contents']['sectionListRenderer']['contents'][1]['itemSectionRenderer']) > 0
        ) {
            $results = $data['contents']['sectionListRenderer']['contents'][1]['itemSectionRenderer']['contents'];
            foreach ($results as $result) {
                if (!empty($result['compactVideoRenderer']['videoId'])) {
                    $item[] = [
                        'id' => $result['compactVideoRenderer']['videoId'],
                        'title' => $result['compactVideoRenderer']['title']['runs'][0]['text'],
                        'time' => $result['compactVideoRenderer']['lengthText']['runs'][0]['text'],
                        'thumbnail' => end($result['compactVideoRenderer']['thumbnail']['thumbnails'])['url']
                    ];
                }
            }
        }

        return $item;
    }
}
