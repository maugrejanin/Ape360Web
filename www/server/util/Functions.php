<?php

//----------------------- debug -----------------------

function dump($var){
	if(is_array($var) or is_object($var))
		throw new Ex_Detail($var);
	else
		throw new Ex_User($var);
}

//----------------------- miscellaneous -----------------------

function get_ip_address() {
    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check if multiple ips exist in var
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (validate_ip($ip))
                    return $ip;
            }
        } else {
            if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];

    // return unreliable ip since all else failed
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
function validate_ip($ip) {
    if (strtolower($ip) === 'unknown')
        return false;

    // generate ipv4 network address
    $ip = ip2long($ip);

    // if the ip is set and not equivalent to 255.255.255.255
    if ($ip !== false && $ip !== -1) {
        // make sure to get unsigned long representation of ip
        // due to discrepancies between 32 and 64 bit OSes and
        // signed numbers (ints default to signed in PHP)
        $ip = sprintf('%u', $ip);
        // do private network range checking
        if ($ip >= 0 && $ip <= 50331647) return false;
        if ($ip >= 167772160 && $ip <= 184549375) return false;
        if ($ip >= 2130706432 && $ip <= 2147483647) return false;
        if ($ip >= 2851995648 && $ip <= 2852061183) return false;
        if ($ip >= 2886729728 && $ip <= 2887778303) return false;
        if ($ip >= 3221225984 && $ip <= 3221226239) return false;
        if ($ip >= 3232235520 && $ip <= 3232301055) return false;
        if ($ip >= 4294967040) return false;
    }
    return true;
}

function checkPostSizeExceeded() {
	if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST' and
		isset($_SERVER['CONTENT_LENGTH']) and empty($_POST)
	) {
		$max = get_ini_bytes('post_max_size');
		$send = $_SERVER['CONTENT_LENGTH'];

		if($max < $_SERVER['CONTENT_LENGTH'])
			Server::treatException(new Ex_User(
				'Volume máximo de dados excedido! Foram enviados ' . 
					number_format($send/(1024*1024), 2) . 'MB, sendo ' . number_format($max/(1024*1024), 2) . 'MB o limite da aplicação.'
				)
			);
	}
}

function get_ini_bytes($attr){
    $attr_value = trim(ini_get($attr));

    if ($attr_value != '') {
        $type_byte = mb_strtolower(
            $attr_value{strlen($attr_value) - 1}
        );
    } else
        return $attr_value;

    switch ($type_byte) {
        case 'g': $attr_value *= 1024*1024*1024; break;
        case 'm': $attr_value *= 1024*1024; break;
        case 'k': $attr_value *= 1024; break;
    }

    return $attr_value;
}

function fastUpload($root, $path, $file, $file_name, $encript_name = false, &$info = null){
	if(is_array($file)){
		$upload = Upload::factory($path, $root);
		$upload->file($file);
		$upload->set_max_file_size(TAMANHO_MAXIMO_UPLOAD_MB);

		$extension = strpos($file_name, '.') !== false? false: pathinfo($file['name'], PATHINFO_EXTENSION);
		$file_name = ($encript_name? uniqid($file_name, true): $file_name) . ($extension? '.' . $extension: '');
		
		$upload->set_filename($file_name);
		$upload_result = $upload->upload();

		if($info)
			$info = $upload_result;

		if($upload_result['status'])
			return $file_name;
		else
			return false;
	}else
		return uploadBase64($root, $path, $file, $file_name, $encript_name);
}

function uploadBase64($root, $path, $base64img, $file_name, $encript_name = false){
    //$base64img = str_replace('data:image/jpeg;base64,', '', $base64img);

    $data = base64_decode($base64img);
	$base64img = preg_match('/data\:image\/(jpeg|jpg|png|gif)\;base64\,/', $base64img, $header_64);
	
	$extension = $header_64[1];	
    $file_name = ($encript_name? uniqid($file_name, true): $file_name) . '.' . $extension;

    if(file_put_contents($root . $path . $file_name, $data))
    	return $encript_name? $file_name: true;
	else
		return false;
}

function require_profile($profiles){
	global $user_log;

	$user_profile = $user_log->id_usuario_perfil;

	if(is_array($profiles)){
		if(!in_array($user_profile, $profiles))
			throw new Ex_Permit('Acesso negado');
	}elseif($user_profile != $profiles)
		throw new Ex_Permit('Acesso negado');
}

function require_post(){
	$args = is_array( func_get_arg(0) )? func_get_arg(0) : func_get_args();
	$not_set = [];

	foreach ($args as $value)
		if( !isset($_POST[$value]) )
			$not_set[] = $value;

	if(!empty($not_set))
		throw new Exception("As informações fornecidas são insuficientes. Está faltando: " . implode(", ", $not_set));

	return array_sub_order($_POST, $args);
}


//explain: http://php.net/manual/pt_BR/features.file-upload.multiple.php#53240
function file_reorder(&$file_post) {

	if(!$file_post)
		return [];

    $file_ary = array();
    $file_keys = array_keys($file_post);
    $file_keys_inside = array_keys($file_post['name']);

    foreach($file_keys_inside as $keys_inside)
        foreach ($file_keys as $key)
            $file_ary[$keys_inside][$key] = $file_post[$key][$keys_inside];

    return $file_ary;
}

//----------------------- array -----------------------

function array_sub_order(array $values, array $keys){
	$keys = array_flip($keys);
	return array_merge(array_intersect_key($keys, $values), array_intersect_key($values, $keys));
}

function array_order(array $values, array $keys){
	return array_merge(array_flip($keys), $values);
}

function array_sub(array $values, array $keys){
	return array_intersect_key($values, array_flip($keys));
}

function array_key_column(array $values, $column){
	return array_combine(array_column($values, $column), $values);
}

function array_count_dim($values){
    $max_indentation = 1;

    $array_str = print_r($values, true);
    $lines = explode("\n", $array_str);

    foreach ($lines as $line) {
    	$indentation = (strlen($line) - strlen(ltrim($line))) / 4;

    	if ($indentation > $max_indentation) {
    		$max_indentation = $indentation;
    	}
    }

    return ceil(($max_indentation - 1) / 2) + 1;
}

function array_group(array $matrix, $col, $keepkeys = false){
	$new_matrix = [];

	if($keepkeys)
		foreach ($matrix as $key => $entry)
		    $new_matrix[$entry[$col]][$key] = $entry;
	else
		foreach ($matrix as $entry)
		    $new_matrix[$entry[$col]][] = $entry;

	return $new_matrix;
}

//----------------------- string -----------------------

function str_query_bind($string, $data) {
    $indexed=$data==array_values($data);

    foreach($data as $k=>$v) {
        if(is_string($v)) $v="'$v'";
        if($indexed) $string=preg_replace('/\?/',$v,$string,1);
        else $string=str_replace(":$k",$v,$string);
    }

    return $string;
}

function get_numeric($val) { 
	if (is_numeric($val)) { 
		return $val + 0; 
	} 
	return $val; 
} 