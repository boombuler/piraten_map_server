<?php
/**
 * @author Nikolai Kordulla
 */
class PBScalar extends PBMessage
{
	/**
	 * Set scalar value
	 */
	public function set_value($value)
	{	
		$this->value = $value;	
	}

	/**
	 * Get the scalar value
	 */
	public function get_value()
	{
		return $this->value;
	}
	
	
	public function toJson($fieldName = "")
	{		
		if ($fieldName == "")
			return  '"'. $this->value . '"';
		else
			return  '"' . $fieldName . '": "' . $this->value . '"';
	}
	
	public function toXML($depth = 0, $rootOrInArray = true)
	{				
		if (is_numeric($this->value))
			return $this->value; 
		else
			return '<![CDATA[' . $this->value . ']]>';
	}
	
	protected function clean()
	{
		unset($this->reader);		
		unset($this->values);
		unset($this->chunk);
		unset($this->_d_string);
	}
}
?>
