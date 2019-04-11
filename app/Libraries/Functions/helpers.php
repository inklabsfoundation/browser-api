<?php

if(!function_exists('bchexdec')) {
    function bchexdec($hex) :string
    {
        if (strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, bchexdec($remain)), hexdec($last));
        }
    }
}

if(!function_exists('bcdechex')) {
    function bcdechex($dec) :string
    {
        $last = bcmod($dec, 16);
        $remain = bcdiv(bcsub($dec, $last), 16);

        if ($remain == 0) {
            return dechex($last);
        } else {
            return bcdechex($remain) . dechex($last);
        }
    }
}

if(!function_exists('get_contract_address_from_tx')){
    function get_contract_address_from_tx(string $tx_id, int $n) :string {
        // 倒序，加vout n, sha160
        $tx_byte_str = '';
        for($i=strlen($tx_id)-2; $i>=0; $i-=2){
            $tx_byte_str .= $tx_id[$i].$tx_id[$i+1];
        }


        $tx_index_byte_str = '';
        $t = str_pad(dechex($n), 8, '0',STR_PAD_LEFT);
        for($i=strlen($t)-2; $i>=0; $i-=2){
            $tx_index_byte_str .= $t[$i].$t[$i+1];
        }

        $byte_str = $tx_byte_str . $tx_index_byte_str;

        return hash160(hex2bin($byte_str));
    }
}

if(!function_exists('escape_hidden_char')){
    function escape_hidden_char(string $text) :string {
        return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    }
}

if(!function_exists('hash256')){
    function hash256(string $data) :string {
        return hash('sha256', hex2bin(hash('sha256', $data)));
    }
}

if(!function_exists('hash160')){
    function hash160(string $data) :string {
        return hash('ripemd160', hex2bin(hash('sha256', $data)));
    }
}

if(!function_exists('bug_report')){
    function bug_report($message){
        $client_obj = new \GuzzleHttp\Client();
        $client_obj->post('https://rocket.headfile.net/hooks/Ys7QmTZbYEEqtcQJt/ZTtt9gWBDqRbffN6e53xkHPPW5d6fcC42YYRhASYNiPWaLtx', [
            'json' => ['text'=>$message],
        ]);
    }
}