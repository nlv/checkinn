<?php

class Authenticity {

    /**
     * Проверка достоверности/недостоверности ИНН
     * $inn ИНН клиента
     * @return [
     *  'status' => 'ok'
     *  'inn' => <ИНН клиента>
     *  'message' => <Текстовый результат проверки>
     *  'authentity' => boolean <признак достоверности>
     * ] |
     * [
     *  'status' => 'error'
     *  'errmsg' => <Ошибка получения ответа с сервиса проверки>
     * ] 
     * 
     */
    public function get (String $inn) {

        $r = self::post(
            'https://pb.nalog.ru/search-proc.json'
            ,[
                'mode' => 'quick-ogr'
                ,'page' => 1
                ,'pageSize' => 10
                ,'pbCapchaToken' => ''
                ,'query' => $inn
                ,'token' => ''
            ]
            );

        if ($r['status'] === 'error') {
            $res = $r;
        } else {
            $r = $r['result'];

            if (isset($r['ERRORS'])) {
                $res = [
                    'status' => 'error'
                    ,'errmsg' => implode (';', $r['ERRORS']['query']),
                ];

            } else {
                if ($r['ogrfl']['rowCount'] + $r['ogrul']['rowCount'] > 0) {
                    $res = [
                        'status' => 'ok'
                        ,'inn' => $inn
                        ,'message' => 'Наличие признака недостоверности'
                        ,'authenticity' => false
                    ];
                } else {
                    $res = [
                        'status' => 'ok'
                        ,'inn' => $inn
                        ,'message' => 'По заданным критериям поиска сведений не найдено'
                        ,'authenticity' => true
                    ];
                }
            }
        }

        return $res;
    }


    private static function post ($url, $params, $cnt = 5) {
        ob_start();
        $curl_request = curl_init();

        curl_setopt($curl_request, CURLOPT_URL, $url . '?' . http_build_query($params));
        curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_request, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6');

        $curl_result = curl_exec($curl_request);

        $done = FALSE;
        if ($curl_result === FALSE) {
            $res = [
                'status' => 'error',
                'errmsg' => curl_error($curl_request)
            ];
            $done = TRUE;
        }

        if (!$done && $curl_result === '' && $cnt > 0) {
            // периодически возвращает 404-й статус с пустым ответом
            $res = self::post($url, $params, $cnt-1);
            $done = TRUE;
        }

        if (!$done) {
            $curl_result = json_decode($curl_result, true);
            if ($curl_result === NULL) {
                $res = [
                        'status' => 'error',
                        'errmsg' => 'Неверный формат ответа сервиса проверки'
                    ];
            } else {
                $res = [
                            'status' => 'ok',
                            'result' => $curl_result
                        ];

            }
        }

        curl_close($curl_request);

        return $res;
        ob_end_flush();
    }

}

?>
