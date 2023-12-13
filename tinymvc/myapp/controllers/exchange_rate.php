<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Exchange_Rate_Controller extends TinyMVC_Controller
{
    public $exchange_rate_file = 'current_exchange_rate.json';
    public $c_codes = array('EUR','USD','RUB','RON','JPY');

    public function ajax_operations() {
        checkIsAjax();

        $action = $this->uri->segment(3);
        switch($action){
            case 'set_user_currency':
                $curs = cleanInput($_POST["curr_code"]);

                $curs_details = model('Currency')->get_details($curs);

                if(empty($curs_details) || !$curs_details['enable']){
                    jsonResponse('Invalid currency');
                }

                if(!$this->cookies->exist_cookie('currency_key') || $ftime != $this->cookies->cookieArray['currency_time'] || $this->cookies->cookieArray['currency_key'] != $curs){
                    $ftime = filemtime($this->exchange_rate_file);

                    $birja = json_decode(file_get_contents($this->exchange_rate_file), true);

                    $this->cookies->setCookieParam('currency_time', $ftime, time()+3600*12);
                    $this->cookies->setCookieParam('currency_key', $curs, time()+3600*24*100);
                    $this->cookies->setCookieParam('currency_suffix', $curs_details['suffix_class'], time()+3600*24*100);
                    $this->cookies->setCookieParam('currency_value', $birja[$curs], time()+3600*12);
                    $this->cookies->setCookieParam('currency_code', $curs_details['curr_entity'], time()+3600*12);
                }

                jsonResponse('', 'success');
			break;
        }
    }

    function renew_exchange_file_DELETE(){
        $date = date("d.m.Y");
        /**/
        $url = "http://www.bnm.md/md/official_exchange_rates?get_xml=1&date=".$date;
//        echo $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);

        $xml= simplexml_load_string($data);

        $json = array();
        $insert = array();

        $date = date('Y-m-d', strtotime($date));
        foreach($xml->Valute as $row){
            if(!in_array($row->CharCode, $this->c_codes))
                continue;

            $val = floatval(($row->Nominal != 1) ? ($row->Value/$row->Nominal) : $row->Value);
            $insert[] = array(
                'ccode' => (string)$row->CharCode,
                'nomin' => 1,
                'name' => (string)$row->Name,
                'date' => $date,
                'val' => $val
            );

            $json[(string)$row->CharCode] = $val;
        }

//        $json = array(
//            "USD" => 1,
//            "EUR" => 0.92747,
//            "GBP" => 0.66401
//        );
        $json = json_encode($json);

        file_put_contents($this->exchange_rate_file, $json);
//        print_r($insert);
        $this->load->model('Exchange_Rate_Model', 'ex_rate');
        $this->ex_rate->insert_ex_rate($insert);
    }


}
?>
