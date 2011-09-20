<?php
/**
 * Varint type
 * @author Nikolai Kordulla
 */
class PBInt extends PBScalar
{
	var $wired_type = PBMessage::WIRED_VARINT;

	/**
	 * Parses the message for this type
	 *
	 * @param array
	 */
	public function ParseFromArray()
	{
		$this->value = $this->reader->next();
		
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

		$value = $this->base128->set_value($this->value);
		$string .= $value;

		return $string;
	}
	
	public function toJson($fieldName = "")
	{		
		if ($fieldName == "")
			return  (int)$this->value;
		else
			return  '"' . $fieldName . '":' . (int)$this->value;
	}
}
?>
