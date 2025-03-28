<?php

// Defines for JSON tokens and locations
define('JSON_BOOL', 1);
define('JSON_INT', 2);
define('JSON_STR', 3);
define('JSON_FLOAT', 4);
define('JSON_NULL', 5);
define('JSON_START_OBJ', 6);
define('JSON_END_OBJ', 7);
define('JSON_START_ARRAY', 8);
define('JSON_END_ARRAY', 9);
define('JSON_KEY', 10);
define('JSON_SKIP', 11);

define('JSON_IN_ARRAY', 30);
define('JSON_IN_OBJECT', 40);
define('JSON_IN_BETWEEN', 50);

class Moxiecode_JSONReader {
	private $_data;
	private $_len;
	private $_pos = -1;
	private $_value;
	private $_token;
	private $_location = JSON_IN_BETWEEN;
	private $_lastLocations = [];
	private $_needProp = false;

	public function __construct(string $data) {
		$this->_data = $data;
		$this->_len = strlen($data);
	}

	public function getToken() {
		return $this->_token;
	}

	public function getLocation() {
		return $this->_location;
	}

	public function getTokenName() {
		return match ($this->_token) {
			JSON_BOOL => 'JSON_BOOL',
			JSON_INT => 'JSON_INT',
			JSON_STR => 'JSON_STR',
			JSON_FLOAT => 'JSON_FLOAT',
			JSON_NULL => 'JSON_NULL',
			JSON_START_OBJ => 'JSON_START_OBJ',
			JSON_END_OBJ => 'JSON_END_OBJ',
			JSON_START_ARRAY => 'JSON_START_ARRAY',
			JSON_END_ARRAY => 'JSON_END_ARRAY',
			JSON_KEY => 'JSON_KEY',
			default => 'UNKNOWN',
		};
	}

	public function getValue() {
		return $this->_value;
	}

	public function readToken() {
		$chr = $this->read();
		if ($chr !== null) {
			return match ($chr) {
				'[' => $this->startArray(),
				']' => $this->endArray(),
				'{' => $this->startObject(),
				'}' => $this->endObject(),
				'"', '\'' => $this->readString($chr),
				'n' => $this->readNull(),
				't', 'f' => $this->readBool($chr),
				default => is_numeric($chr) || $chr === '-' || $chr === '.' ? $this->readNumber($chr) : true,
			};
		}
		return false;
	}

	private function startArray() {
		$this->_lastLocations[] = $this->_location;
		$this->_location = JSON_IN_ARRAY;
		$this->_token = JSON_START_ARRAY;
		$this->_value = null;
		$this->readAway();
		return true;
	}

	private function endArray() {
		$this->_location = array_pop($this->_lastLocations);
		$this->_token = JSON_END_ARRAY;
		$this->_value = null;
		$this->readAway();
		if ($this->_location === JSON_IN_OBJECT) {
			$this->_needProp = true;
		}
		return true;
	}

	private function startObject() {
		$this->_lastLocations[] = $this->_location;
		$this->_location = JSON_IN_OBJECT;
		$this->_needProp = true;
		$this->_token = JSON_START_OBJ;
		$this->_value = null;
		$this->readAway();
		return true;
	}

	private function endObject() {
		$this->_location = array_pop($this->_lastLocations);
		$this->_token = JSON_END_OBJ;
		$this->_value = null;
		$this->readAway();
		if ($this->_location === JSON_IN_OBJECT) {
			$this->_needProp = true;
		}
		return true;
	}

	private function readBool(string $chr) {
		$this->_token = JSON_BOOL;
		$this->_value = $chr === 't';
		$this->skip($chr === 't' ? 3 : 4); // Skip "rue" or "alse"
		$this->readAway();
		if ($this->_location === JSON_IN_OBJECT && !$this->_needProp) {
			$this->_needProp = true;
		}
		return true;
	}

	private function readNull() {
		$this->_token = JSON_NULL;
		$this->_value = null;
		$this->skip(3); // Skip "ull"
		$this->readAway();
		if ($this->_location === JSON_IN_OBJECT && !$this->_needProp) {
			$this->_needProp = true;
		}
		return true;
	}

	private function readString(string $quote) {
		$output = '';
		$this->_token = JSON_STR;
		$endString = false;

		while (($chr = $this->peek()) !== null) {
			if ($chr === '\\') {
				$this->read(); // Escape
				$output .= $this->readEscape();
			} elseif ($chr === $quote) {
				$endString = true;
				$this->read();
				break;
			} else {
				$output .= $this->read();
			}
		}

		$this->readAway();
		$this->_value = $output;

		if ($this->_needProp) {
			$this->_token = JSON_KEY;
			$this->_needProp = false;
		} elseif ($this->_location === JSON_IN_OBJECT && !$this->_needProp) {
			$this->_needProp = true;
		}
		return true;
	}

	private function readEscape() {
		$chr = $this->read();
		return match ($chr) {
			't' => "\t",
			'b' => "\b",
			'f' => "\f",
			'r' => "\r",
			'n' => "\n",
			'u' => $this->decodeUnicodeEscape(),
			default => $chr,
		};
	}

	private function decodeUnicodeEscape() {
		$hex = $this->read(4);
		return mb_convert_encoding(pack('H*', $hex), 'UTF-8', 'UTF-16BE');
	}

	private function readNumber($start) {
		$value = $start;
		$isFloat = false;

		while (($chr = $this->peek()) !== null && (is_numeric($chr) || $chr === '-' || $chr === '.')) {
			if ($chr === '.') {
				$isFloat = true;
			}
			$value .= $this->read();
		}

		$this->readAway();
		$this->_token = $isFloat ? JSON_FLOAT : JSON_INT;
		$this->_value = $isFloat ? (float)$value : (int)$value;

		if ($this->_location === JSON_IN_OBJECT && !$this->_needProp) {
			$this->_needProp = true;
		}
		return true;
	}

	private function readAway() {
		while (($chr = $this->peek()) !== null && ($chr === ':' || $chr === ',' || ctype_space($chr))) {
			$this->read();
		}
	}

	private function read(int $len = 1): ?string {
		if ($this->_pos < $this->_len - 1) {
			if ($len > 1) {
				$str = substr($this->_data, $this->_pos + 1, $len);
				$this->_pos += $len;
				return $str;
			}
			return $this->_data[++$this->_pos];
		}
		return null;
	}

	private function skip(int $len) {
		$this->_pos += $len;
	}

	private function peek(): ?string {
		return $this->_pos < $this->_len - 1 ? $this->_data[$this->_pos + 1] : null;
	}
}

//
class Moxiecode_JSON
{

	// Properties explicitly declared
	private array $data = [];
	private array $parents = [];
	private mixed $cur = null; // Reference to the current working object/array

	public function __construct()
	{
		// Constructor for initialization if needed
	}

	public function decode(string $input): mixed
	{
		$reader = new Moxiecode_JSONReader($input);
		return $this->readValue($reader);
	}

	private function readValue(Moxiecode_JSONReader $reader): mixed
	{
		$this->data = [];
		$this->parents = [];
		$this->cur = &$this->data;
		$key = null;
		$loc = JSON_IN_ARRAY;

		while ($reader->readToken()) {
			switch ($reader->getToken()) {
				case JSON_STR:
				case JSON_INT:
				case JSON_BOOL:
				case JSON_FLOAT:
				case JSON_NULL:
					match ($reader->getLocation()) {
						JSON_IN_OBJECT => $this->cur[$key] = $reader->getValue(),
						JSON_IN_ARRAY => $this->cur[] = $reader->getValue(),
						default => $reader->getValue(),
					};
					break;

				case JSON_KEY:
					$key = $reader->getValue();
					break;

				case JSON_START_OBJ:
				case JSON_START_ARRAY:
					if ($loc === JSON_IN_OBJECT) {
						$this->addArray($key);
					} else {
						$this->addArray(null);
					}
					$loc = $reader->getLocation();
					break;

				case JSON_END_OBJ:
				case JSON_END_ARRAY:
					$loc = $reader->getLocation();
					if (count($this->parents) > 0) {
						$this->cur = &$this->parents[array_key_last($this->parents)];
						array_pop($this->parents);
					}
					break;
			}
		}

		return $this->data[0] ?? null;
	}

	private function addArray(?string $key): void
	{
		$this->parents[] = &$this->cur;
		$array = [];

		if ($key) {
			$this->cur[$key] = &$array;
		} else {
			$this->cur[] = &$array;
		}

		$this->cur = &$array;
	}

	public function encode(mixed $input): string
	{
		return match (gettype($input)) {
			'boolean' => $input ? 'true' : 'false',
			'integer' => (string)$input,
			'double', 'float' => (string)$input,
			'NULL' => 'null',
			'string' => $this->encodeString($input),
			'array' => $this->_encodeArray($input),
			'object' => $this->_encodeArray(get_object_vars($input)),
			default => '',
		};
	}

	private function encodeString(string $input): string
	{
		// Escape special characters
		$output = preg_replace_callback('/[\b\t\n\f\r"\'\\\\]/', function ($matches) {
			return match ($matches[0]) {
				"\b" => "\\b",
				"\t" => "\\t",
				"\n" => "\\n",
				"\f" => "\\f",
				"\r" => "\\r",
				'"' => '\"',
				'\'' => "\\'",
				'\\' => '\\\\',
			};
		}, $input);

		// Unicode escaping
		$output = preg_replace_callback('/[^\x20-\x7E]/u', function ($matches) {
			$char = $matches[0];
			$utf16 = mb_convert_encoding($char, 'UTF-16BE', 'UTF-8');
			$hex = strtoupper(bin2hex($utf16));
			return "\\u" . str_pad($hex, 4, '0', STR_PAD_LEFT);
		}, $output);

		return '"' . $output . '"';
	}

	private function _encodeArray(array $input): string
	{
		$isIndexed = array_keys($input) === range(0, count($input) - 1);
		$elements = [];

		foreach ($input as $key => $value) {
			if ($isIndexed) {
				$elements[] = $this->encode($value);
			} else {
				$elements[] = $this->encodeString((string)$key) . ':' . $this->encode($value);
			}
		}

		return $isIndexed ? '[' . implode(',', $elements) . ']' : '{' . implode(',', $elements) . '}';
	}
}

