<?php
/**
 * @author Nikolai Kordulla
 */
class PBBytes extends PBScalar
{
	var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;

	/**
	 * Parses the message for this type
	 *
	 * @param array
	 */
	public function ParseFromArray()
	{
		$this->value = '';
		// first byte is length
		$length = $this->reader->next();

		// just extract the string
		$pointer = $this->reader->get_pointer();
		$this->reader->add_pointer($length);
		$this->value = $this->reader->get_message_from($pointer);
		
		$this->clean();
	}

	/**
	 * Serializes type
	 */
	public function SerializeToString($rec = -1)
	{
		$string = '';

		if ($rec > -1)
		{
			$string .= $this->base128->set_value($rec << 3 | $this->wired_type);
		}

		$string .= $this->base128->set_value(strlen($this->value));
		$string .= $this->value;

		return $string;
	}
	
	public function toJson($fieldName = "")
	{		
		if ($fieldName == "")
			return  '"[BLOB] (' . strlen($this->value) . ' bytes)"';
		else
			return  '"' . $fieldName . '": "[BLOB] (' . strlen($this->value) . ' bytes)"';
	}
}
?>
