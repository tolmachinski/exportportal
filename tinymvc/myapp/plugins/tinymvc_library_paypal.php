<?php
/********* PayPal with IPN in PHP **********/
/**
 *
 * @author Bendiucov Tatiana
 * @todo Remove [03.12.2021]
 * Not used
 */
class TinyMVC_Library_Paypal {
    var $error; // holds the error encountered
    var $ipn_log; // log IPN results
    var $ipn_log_file; // filename of the IPN log
    var $ipn_response; // holds the IPN response from PayPal
    var $ipn_data = array(); // contains the POST values for IPN
    var $fields = array(); // holds the fields to submit to PayPal
    var $email = ''; // holds the fields to submit to PayPal

    function __construct() {
        // constructor.
        $this->ipn_url = 'https://ipnpb.paypal.com/cgi-bin/webscr';
        $this->paypal_url = DEBUG_MODE ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
        $this->error = '';
        $this->ipn_log_file = 'ipn_results.log';
        $this->ipn_log = true;
        $this->ipn_response = '';
        $this->add_field('rm','2'); // Return method = POST
        $this->add_field('cmd','_xclick');
        $this->email = 'pay@exportportal.com'; // ep.pp.acc@gmail.com Password: ep_paypal2015
    }

    function add_field($field, $value) {
        $this->fields["$field"] = $value;
    }

    function submit_paypal_post() {
        // generates an HTML page consisting of
        // a form with hidden elements which is submitted to PayPal
        $out = "";
        //$out .=  "<script type='text/javascript'>document.onload = \"document.forms['paypal_form'].submit();\"</script>;
        $out .=  "<form method=\"post\" name=\"paypal_form\" action=\"".$this->paypal_url."\" class='paypall'>\n";

        foreach ($this->fields as $name => $value) {
            $out .=  "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
        }
        $out .=  "<button class=\"btn btn-primary\" type=\"submit\">" . translate('billing_documents_step3_go_to_paypal') . "</button>\n";
        $out .=  "</form>\n";
        return $out;
    }

    function validate_ipn() {
        // parse the paypal URL
        $url_parsed = parse_url($this->paypal_url);

        // generate the post string from the _POST vars
        $post_string = '';
        foreach ($_POST as $field => $value) {
            $this->ipn_data["$field"] = $value;
            $post_string .= $field.'='.urlencode (stripslashes ($value)).'&';
        }
        $post_string .= "cmd=_notify-validate";
        // open the connection to paypal
        $curl = curl_init($this->ipn_url);

        if(!$curl) {
        // Print the error if not able to open the connection.
            $this->error = "Got " . curl_error($curl) . " when processing IPN data";
            $this->log_ipn_results(false);
            return false;
        } else {
			curl_setopt ($curl, CURLOPT_HEADER, 0);
			curl_setopt ($curl, CURLOPT_POST, 1);
			curl_setopt ($curl, CURLOPT_POSTFIELDS, $post_string);
			curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 1);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: company-name'));
			$response = curl_exec ($curl);
			curl_close ($curl);
        }
        //print_r($response);
        if ($response == "VERIFIED") {
            // Valid IPN.
            $this->log_ipn_results(true);
			//echo "Verified";
            return true;
        } else {
            // Invalid IPN.
            $this->error = 'IPN Validation Failed.';
            $this->log_ipn_results(false);
			//echo "Error";
            return false;
        }
    }

    function log_ipn_results($success) {
        if (!$this->ipn_log) return;
        // Timestamp
        $text = '['.date('m/d/Y g:i A').'] - ';
        // Success or failure
        if ($success) $text .= "SUCCESS!\n";
        else $text .= 'FAIL: '.$this->error."\n";
        // Log the POST variables
        $text .= "IPN POST Values from Paypal:\n";
        foreach ($this->ipn_data as $key=>$value) {
            $text .= "$key=$value, ";
        }
        // response from the paypal server
        $text .= "\nIPN Response from Paypal Server:\n ".$this->ipn_response;
        // Write to log
        $fp=fopen($this->ipn_log_file,'a');
        fwrite($fp, $text . "\n\n");
        fclose($fp); // close file
    }
}
