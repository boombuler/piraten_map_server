<?php
/**
 * @author Nikolai Kordulla
 */
class PB64Bit extends PBScalar
{
	var $wired_type = PBMessage::WIRED_64BIT;

	/**
	 * Parses the message for this type
	 *
	 * @param array
	 */
	public function ParseFromArray()
	{					
		$pointer = $this->reader->get_pointer();
		$this->reader->add_pointer(8);
		$str = $this->reader->get_message_from($pointer);
		
		$p = unpack("d", $str);
		$this->value = $p[1];  		
		
		$this->clean();
	}

	/**
	 * Serializes type
	 */
	public function SerializeToString($rec=-1)
	{
		// first byte is length byte
		$string = '';

		if ($rec > -1)
		{
			$string .= $this->base128->set_value($rec << 3 | $this->wired_type);
		}

		//$value = $this->base128->set_value($this->value);
		$value = pack("d", $this->value);
		$string .= $value;

		return $string;
	}
	
	public function toJson($fieldName = "")
	{		
		if ($fieldName == "")
			return  $this->value;
		else
			return  '"' . $fieldName . '":' . $this->value;
	}
}
?>
