<?PHP

function php_compat_str_ireplace($search, $replace, $subject, &$count)
{
	// Sanity check
	if (is_string($search) && is_array($replace)) {
		user_error('Array to string conversion', E_USER_NOTICE);
		$replace = (string) $replace;
	}

	// If search isn't an array, make it one
	if (!is_array($search)) {
		$search = array ($search);
	}
	$search = array_values($search);

	// If replace isn't an array, make it one, and pad it to the length of search
	if (!is_array($replace)) {
		$replace_string = $replace;

		$replace = array ();
		for ($i = 0, $c = count($search); $i < $c; $i++) {
			$replace[$i] = $replace_string;
		}
	}
	$replace = array_values($replace);

	// Check the replace array is padded to the correct length
	$length_replace = count($replace);
	$length_search = count($search);
	if ($length_replace < $length_search) {
		for ($i = $length_replace; $i < $length_search; $i++) {
			$replace[$i] = '';
		}
	}

	// If subject is not an array, make it one
	$was_array = false;
	if (!is_array($subject)) {
		$was_array = true;
		$subject = array ($subject);
	}

	// Prepare the search array
	foreach ($search as $search_key => $search_value) {
		$search[$search_key] = '/' . preg_quote($search_value, '/') . '/i';
	}

	// Prepare the replace array (escape backreferences)
	foreach ($replace as $k => $v) {
		$replace[$k] = str_replace(array(chr(92), '$'), array(chr(92) . chr(92), '\$'), $v);
	}

	// do the replacement
	$result = preg_replace($search, $replace, $subject);

	// Check if subject was initially a string and return it as a string
	if ($was_array === true) {
		return $result[0];
	}

	// Otherwise, just return the array
	return $result;
}
// Define
if (!function_exists('str_ireplace')) {
	function str_ireplace($search, $replace, $subject, $count = null)
	{
		return php_compat_str_ireplace($search, $replace, $subject, $count);
	}
}

function php_compat_stripos($haystack, $needle, $offset = null)
{
	if (!is_scalar($haystack)) {
		user_error('stripos() expects parameter 1 to be string, ' .
		gettype($haystack) . ' given', E_USER_WARNING);
		return false;
	}

	if (!is_scalar($needle)) {
		user_error('stripos() needle is not a string or an integer.', E_USER_WARNING);
		return false;
	}

	if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
		user_error('stripos() expects parameter 3 to be long, ' .
		gettype($offset) . ' given', E_USER_WARNING);
		return false;
	}

	// Manipulate the string if there is an offset
	$fix = 0;
	if (!is_null($offset)) {
		if ($offset > 0) {
			$haystack = substr($haystack, $offset, strlen($haystack) - $offset);
			$fix = $offset;
		}
	}

	$segments = explode(strtolower($needle), strtolower($haystack), 2);

	// Check there was a match
	if (count($segments) === 1) {
		return false;
	}

	$position = strlen($segments[0]) + $fix;
	return $position;
}
// Define
if (!function_exists('stripos')) {
	function stripos($haystack, $needle, $offset = null)
	{
		return php_compat_stripos($haystack, $needle, $offset);
	}
}

function php_compat_array_combine($keys, $values)
{
	if (!is_array($keys)) {
		user_error('array_combine() expects parameter 1 to be array, ' .
		gettype($keys) . ' given', E_USER_WARNING);
		return;
	}

	if (!is_array($values)) {
		user_error('array_combine() expects parameter 2 to be array, ' .
		gettype($values) . ' given', E_USER_WARNING);
		return;
	}

	$key_count = count($keys);
	$value_count = count($values);
	if ($key_count !== $value_count) {
		user_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
		return false;
	}

	if ($key_count === 0 || $value_count === 0) {
		user_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
		return false;
	}

	$keys    = array_values($keys);
	$values  = array_values($values);

	$combined = array();
	for ($i = 0; $i < $key_count; $i++) {
		$combined[$keys[$i]] = $values[$i];
	}

	return $combined;
}
// Define
if (!function_exists('array_combine')) {
	function array_combine($keys, $values)
	{
		return php_compat_array_combine($keys, $values);
	}
}

?>