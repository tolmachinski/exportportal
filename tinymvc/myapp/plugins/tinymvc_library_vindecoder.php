<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring: code style, optimize code
 */
class TinyMVC_Library_Vindecoder
{
    public $tmvc;

    // CONFIGURABLE PARMETERS
    private $id = 'F011912M';
    private $password = 'W121901Y';
    private $report_type = 'BASIC';

    /**
     * Contains decoded VIN codes.
     *
     * @var array
     */
    private $decoded_codes = array();

    public function decode($vin_code, $result_type = 'array')
    {
        if (!isset($this->decoded_codes[$vin_code])) {
            $this->decoded_codes[$vin_code] = $this->fetch_vin_code($vin_code);
        }

        $decoded_vin = $this->decoded_codes[$vin_code];
        $parsing = simplexml_load_string($decoded_vin, 'SimpleXMLElement', LIBXML_NOCDATA);
        $result = array();
        if (false !== $parsing) {
            $info = $parsing->REPORT->VINPOWER->VIN->DECODED;
            $items = $info->ITEM;
            switch ($result_type) {
                default:
                case 'array':
                    foreach ($items as $item) {
                        $result[] = array(
                            'name'  => "{$item['name']}",
                            'value' => "{$item['value']}",
                        );
                    }

                break;
                case 'string':
                    foreach ($items as $item) {
                        $result[] = "{$item['name']} {$item['value']}";
                    }
                    $result = implode(' ', $result);

                break;
                case 'both':
                    foreach ($items as $item) {
                        $result['array'][] = array(
                            'name'  => "{$item['name']}",
                            'value' => "{$item['value']}",
                        );
                        $result['string'][] = "{$item['name']} {$item['value']}";
                    }
                    $result['string'] = implode(' ', $result['string']);

                break;
            }
        }

        return $result;
    }

    public function is_used($vin_code)
    {
        $exist = model('items')->control_vin($vin_code);
        if ($exist > 0) {
            return true;
        }

        return false;
    }

    /**
     * Fetches the decoded VIN information from another server.
     *
     * @param string $code
     *
     * @return string
     */
    private function fetch_vin_code($code)
    {
        $url = 'http://service.vinlink.com/report?type=' . $this->report_type . '&vin=' . $code;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->id:$this->password");
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}
