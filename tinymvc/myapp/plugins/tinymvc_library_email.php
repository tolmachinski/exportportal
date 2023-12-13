<?php

/**
 *
 * @author Bendiucov Tatiana
 * @todo Remove [01.12.2021]
 * Library not used
 */
class TinyMVC_Library_Email {

    function send($data, $subject, $message, $header=null) {
        foreach($data as $key => $value) {
            $message = str_replace("[site_url]", __SITE_URL, $message);
            $message = str_replace("[$key]", $value, $message);
        }

        if(!isset($header))
            $header = "info@exportportal.com";
        $headers = "From: " . $header . "\r\n";
        $headers .= "Reply-To: " . $header . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        //echo "<-- ".$data['email']."<br/> $subject <br/> $message, <br/>";
        mail($data['email'], $subject, $message, $headers);
    }


    // test function
    function send_mail($mail_to,$fffrom, $thema, $html, $path)
    {
    if ($path)
    {
        $fp = fopen($path,"rb");
        $file = fread($fp, filesize($path));
        fclose($fp);
    }
    $name = basename($path); // в этой переменной надо сформировать имя файла (без всякого пути)
    $EOL = "\r\n"; // ограничитель строк, некоторые почтовые сервера требуют \n - подобрать опытным путём
    $boundary     = "--".md5(uniqid(time()));  // любая строка, которой не будет ниже в потоке данных.
    $headers    = "MIME-Version: 1.0;$EOL";
    $headers   .= "Content-Type: multipart/mixed; boundary=\"$boundary\"$EOL";
    $headers   .= "From: Администрация сайта <$fffrom>";

    $multipart  = "--$boundary$EOL";
    $multipart .= "Content-Type: text/html; charset=windows-1251$EOL";
    $multipart .= "Content-Transfer-Encoding: base64$EOL";
    $multipart .= $EOL; // раздел между заголовками и телом html-части
    $multipart .= chunk_split(base64_encode($html));
    $multipart .=  "$EOL--$boundary$EOL";
    if (file_exists($path))
    {
    $multipart .= "Content-Type: application/octet-stream; name=\"$name\"$EOL";
    $multipart .= "Content-Transfer-Encoding: base64$EOL";
    $multipart .= "Content-Disposition: attachment; filename=\"$name\"$EOL";
    $multipart .= $EOL; // раздел между заголовками и телом прикрепленного файла
    $multipart .= chunk_split(base64_encode($file));
    $multipart .= "$EOL--$boundary--$EOL";
    }
    if(!mail($mail_to, $thema, $multipart, $headers))
    {
        return False;           //если не письмо не отправлено
    }
    else
    {
        return True;
    }
    exit;
    }



function xmail( $from, $to, $subj, $text, $filename=null,$filename2=null) {
   $f         = fopen($filename,"rb");
   $un        = strtoupper(uniqid(time()));
   $head      = "From: $from\n";
   $head     .= "To: $to\n";
   $head     .= "Subject: $subj\n";
   $head     .= "X-Mailer: PHPMail Tool\n";
   $head     .= "Reply-To: $from\n";
   $head     .= "Mime-Version: 1.0\n";
   $head     .= "Content-Type:multipart/mixed;";
   $head     .= "boundary=\"----------".$un."\"\n\n";
   $zag       = "------------".$un."\nContent-Type:text/html;\n";
   $zag      .= "Content-Transfer-Encoding: 8bit\n\n$text\n\n";
   $zag      .= "------------".$un."\n";
    if (!empty($filename)){
       $zag      .= "Content-Type: application/octet-stream;";
       $zag      .= "name=\"".basename($filename2)."\"\n";
       $zag      .= "Content-Transfer-Encoding:base64\n";
       $zag      .= "Content-Disposition:attachment;";
       $zag      .= "filename=\"".basename($filename2)."\"\n\n";
       $zag      .= chunk_split(base64_encode(fread($f,filesize($filename))))."\n";
    }
   if (!@mail($to, $subj, $zag, $head))
   return 0;
   else
   return 1;
}

function htmlimgmail($mail_to, $thema, $html, $path, $from)
{
    $EOL = "\n";
    $boundary = "--".md5(uniqid(time()));
    $headers = "MIME-Version: 1.0;$EOL";
    $headers .= "From: $from$EOL";
    $headers .= "Content-Type: multipart/related; boundary=\"$boundary\"$EOL";

    $multipart = "--{$boundary}$EOL";
    $multipart .= "Content-Type: text/html; charset=koi8-r$EOL";
    $multipart .= "Conteny-Transfer-Encoding: 8bit$EOL";
    $multipart .= $EOL;

    if($EOL == "\n") $multipart .= str_replace("\r\n", $EOL, $html);
    $multipart .= $EOL;

    if(!empty($path))
    {
        for($i = 0; $i < count($path); $i++)
        {
            $file = file_get_contents($path[$i]);
            $name = basename($path[$i]);
            $multipart .= "$EOL--$boundary$EOL";
            $multipart .= "Content-Type: image/jpeg; name=\"$name\"$EOL";
            $multipart .= "Content-Transfer-Encoding: base64$EOL";
            $multipart .= "Content-ID: <".md5($name).">$EOL";
            $multipart .= $EOL;
            $multipart .= chunk_split(base64_encode($file), 76, $EOL);
        }
    }
    $multipart .= "$EOL--$boundary--$EOL";
    if(!is_array($mail_to))
    {
        // Single destination
        if(!mail($mail_to, $thema, $multipart, $headers))
        return False;
        else
        return True;
        exit;
    }
    else
    {
        // Multiple destination
        foreach($mail_to as $mail)
        {
            mail($mail, $thema, $multipart, $headers);
        }
    }
}


}// end class
