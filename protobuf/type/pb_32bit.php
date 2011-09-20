<?php
/**
 * @author Nikolai Kordulla
 */
class PB32Bit extends PBScalar
{
	protected $wired_type = PBMessage::WIRED_32BIT;

	/**
	 * Parses the message for this type
	 *
	 * @param array
	 */
	public function ParseFromArray()
	{					
		$pointer = $this->reader->get_pointer();
		$this->reader->add_pointer(4);
		$str = $this->reader->get_message_from($pointer);
		
		$p = unpack("f", $str);
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
		$value = pack("f", $this->value);
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
