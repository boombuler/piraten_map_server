<?php

class Plakat extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    self::$fields["Plakat"]["1"] = "PBInt";
    $this->values["1"] = "";
    self::$fieldNames["Plakat"]["1"] = "Id";
    self::$fields["Plakat"]["2"] = "PB64Bit";
    $this->values["2"] = "";
    self::$fieldNames["Plakat"]["2"] = "Lon";
    self::$fields["Plakat"]["3"] = "PB64Bit";
    $this->values["3"] = "";
    self::$fieldNames["Plakat"]["3"] = "Lat";
    self::$fields["Plakat"]["4"] = "PBString";
    $this->values["4"] = "";
    self::$fieldNames["Plakat"]["4"] = "Type";
    self::$fields["Plakat"]["5"] = "PBString";
    $this->values["5"] = "";
    self::$fieldNames["Plakat"]["5"] = "LastModifiedUser";
    self::$fields["Plakat"]["6"] = "PBInt";
    $this->values["6"] = "";
    self::$fieldNames["Plakat"]["6"] = "LastModifiedTime";
    self::$fields["Plakat"]["7"] = "PBString";
    $this->values["7"] = "";
    self::$fieldNames["Plakat"]["7"] = "Comment";
    self::$fields["Plakat"]["8"] = "PBString";
    $this->values["8"] = "";
    self::$fieldNames["Plakat"]["8"] = "ImageUrl";
  }
  function Id()
  {
    return $this->_get_value("1");
  }
  function set_Id($value)
  {
    return $this->_set_value("1", $value);
  }
  function Lon()
  {
    return $this->_get_value("2");
  }
  function set_Lon($value)
  {
    return $this->_set_value("2", $value);
  }
  function Lat()
  {
    return $this->_get_value("3");
  }
  function set_Lat($value)
  {
    return $this->_set_value("3", $value);
  }
  function Type()
  {
    return $this->_get_value("4");
  }
  function set_Type($value)
  {
    return $this->_set_value("4", $value);
  }
  function LastModifiedUser()
  {
    return $this->_get_value("5");
  }
  function set_LastModifiedUser($value)
  {
    return $this->_set_value("5", $value);
  }
  function LastModifiedTime()
  {
    return $this->_get_value("6");
  }
  function set_LastModifiedTime($value)
  {
    return $this->_set_value("6", $value);
  }
  function Comment()
  {
    return $this->_get_value("7");
  }
  function set_Comment($value)
  {
    return $this->_set_value("7", $value);
  }
  function ImageUrl()
  {
    return $this->_get_value("8");
  }
  function set_ImageUrl($value)
  {
    return $this->_set_value("8", $value);
  }
}
class BoundingBox extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    self::$fields["BoundingBox"]["1"] = "PB64Bit";
    $this->values["1"] = "";
    self::$fieldNames["BoundingBox"]["1"] = "North";
    self::$fields["BoundingBox"]["2"] = "PB64Bit";
    $this->values["2"] = "";
    self::$fieldNames["BoundingBox"]["2"] = "East";
    self::$fields["BoundingBox"]["3"] = "PB64Bit";
    $this->values["3"] = "";
    self::$fieldNames["BoundingBox"]["3"] = "South";
    self::$fields["BoundingBox"]["4"] = "PB64Bit";
    $this->values["4"] = "";
    self::$fieldNames["BoundingBox"]["4"] = "West";
  }
  function North()
  {
    return $this->_get_value("1");
  }
  function set_North($value)
  {
    return $this->_set_value("1", $value);
  }
  function East()
  {
    return $this->_get_value("2");
  }
  function set_East($value)
  {
    return $this->_set_value("2", $value);
  }
  function South()
  {
    return $this->_get_value("3");
  }
  function set_South($value)
  {
    return $this->_set_value("3", $value);
  }
  function West()
  {
    return $this->_get_value("4");
  }
  function set_West($value)
  {
    return $this->_set_value("4", $value);
  }
}
class ViewRequest extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    self::$fields["ViewRequest"]["1"] = "PBString";
    $this->values["1"] = "";
    self::$fieldNames["ViewRequest"]["1"] = "Filter_Type";
    self::$fields["ViewRequest"]["2"] = "BoundingBox";
    $this->values["2"] = "";
    self::$fieldNames["ViewRequest"]["2"] = "ViewBox";
  }
  function Filter_Type()
  {
    return $this->_get_value("1");
  }
  function set_Filter_Type($value)
  {
    return $this->_set_value("1", $value);
  }
  function ViewBox()
  {
    return $this->_get_value("2");
  }
  function set_ViewBox($value)
  {
    return $this->_set_value("2", $value);
  }
}
class ChangeRequest extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    self::$fields["ChangeRequest"]["1"] = "PBInt";
    $this->values["1"] = "";
    self::$fieldNames["ChangeRequest"]["1"] = "Id";
    self::$fields["ChangeRequest"]["2"] = "PBString";
    $this->values["2"] = "";
    self::$fieldNames["ChangeRequest"]["2"] = "Type";
    self::$fields["ChangeRequest"]["3"] = "PBString";
    $this->values["3"] = "";
    self::$fieldNames["ChangeRequest"]["3"] = "Comment";
    self::$fields["ChangeRequest"]["4"] = "PBString";
    $this->values["4"] = "";
    self::$fieldNames["ChangeRequest"]["4"] = "ImageUrl";
  }
  function Id()
  {
    return $this->_get_value("1");
  }
  function set_Id($value)
  {
    return $this->_set_value("1", $value);
  }
  function Type()
  {
    return $this->_get_value("2");
  }
  function set_Type($value)
  {
    return $this->_set_value("2", $value);
  }
  function Comment()
  {
    return $this->_get_value("3");
  }
  function set_Comment($value)
  {
    return $this->_set_value("3", $value);
  }
  function ImageUrl()
  {
    return $this->_get_value("4");
  }
  function set_ImageUrl($value)
  {
    return $this->_set_value("4", $value);
  }
}
class DeleteRequest extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    self::$fields["DeleteRequest"]["1"] = "PBInt";
    $this->values["1"] = "";
    self::$fieldNames["DeleteRequest"]["1"] = "Id";
  }
  function Id()
  {
    return $this->_get_value("1");
  }
  function set_Id($value)
  {
    return $this->_set_value("1", $value);
  }
}
class AddRequest extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    self::$fields["AddRequest"]["1"] = "PB64Bit";
    $this->values["1"] = "";
    self::$fieldNames["AddRequest"]["1"] = "Lon";
    self::$fields["AddRequest"]["2"] = "PB64Bit";
    $this->values["2"] = "";
    self::$fieldNames["AddRequest"]["2"] = "Lat";
    self::$fields["AddRequest"]["3"] = "PBString";
    $this->values["3"] = "";
    self::$fieldNames["AddRequest"]["3"] = "Type";
    self::$fields["AddRequest"]["4"] = "PBString";
    $this->values["4"] = "";
    self::$fieldNames["AddRequest"]["4"] = "Comment";
    self::$fields["AddRequest"]["5"] = "PBString";
    $this->values["5"] = "";
    self::$fieldNames["AddRequest"]["5"] = "ImageUrl";
  }
  function Lon()
  {
    return $this->_get_value("1");
  }
  function set_Lon($value)
  {
    return $this->_set_value("1", $value);
  }
  function Lat()
  {
    return $this->_get_value("2");
  }
  function set_Lat($value)
  {
    return $this->_set_value("2", $value);
  }
  function Type()
  {
    return $this->_get_value("3");
  }
  function set_Type($value)
  {
    return $this->_set_value("3", $value);
  }
  function Comment()
  {
    return $this->_get_value("4");
  }
  function set_Comment($value)
  {
    return $this->_set_value("4", $value);
  }
  function ImageUrl()
  {
    return $this->_get_value("5");
  }
  function set_ImageUrl($value)
  {
    return $this->_set_value("5", $value);
  }
}
class Request extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    self::$fields["Request"]["1"] = "PBString";
    $this->values["1"] = "";
    self::$fieldNames["Request"]["1"] = "Username";
    self::$fields["Request"]["2"] = "PBString";
    $this->values["2"] = "";
    self::$fieldNames["Request"]["2"] = "Password";
    self::$fields["Request"]["3"] = "ViewRequest";
    $this->values["3"] = "";
    self::$fieldNames["Request"]["3"] = "ViewRequest";
    self::$fields["Request"]["4"] = "AddRequest";
    $this->values["4"] = array();
    self::$fieldNames["Request"]["4"] = "Add";
    self::$fields["Request"]["5"] = "ChangeRequest";
    $this->values["5"] = array();
    self::$fieldNames["Request"]["5"] = "Change";
    self::$fields["Request"]["6"] = "DeleteRequest";
    $this->values["6"] = array();
    self::$fieldNames["Request"]["6"] = "Delete";
  }
  function Username()
  {
    return $this->_get_value("1");
  }
  function set_Username($value)
  {
    return $this->_set_value("1", $value);
  }
  function Password()
  {
    return $this->_get_value("2");
  }
  function set_Password($value)
  {
    return $this->_set_value("2", $value);
  }
  function ViewRequest()
  {
    return $this->_get_value("3");
  }
  function set_ViewRequest($value)
  {
    return $this->_set_value("3", $value);
  }
  function Add($offset)
  {
    return $this->_get_arr_value("4", $offset);
  }
  function add_Add()
  {
    return $this->_add_arr_value("4");
  }
  function set_Add($index, $value)
  {
    $this->_set_arr_value("4", $index, $value);
  }
  function set_all_Adds($values)
  {
    return $this->_set_arr_values("4", $values);
  }
  function remove_last_Add()
  {
    $this->_remove_last_arr_value("4");
  }
  function Adds_size()
  {
    return $this->_get_arr_size("4");
  }
  function get_Adds()
  {
    return $this->_get_value("4");
  }
  function Change($offset)
  {
    return $this->_get_arr_value("5", $offset);
  }
  function add_Change()
  {
    return $this->_add_arr_value("5");
  }
  function set_Change($index, $value)
  {
    $this->_set_arr_value("5", $index, $value);
  }
  function set_all_Changes($values)
  {
    return $this->_set_arr_values("5", $values);
  }
  function remove_last_Change()
  {
    $this->_remove_last_arr_value("5");
  }
  function Changes_size()
  {
    return $this->_get_arr_size("5");
  }
  function get_Changes()
  {
    return $this->_get_value("5");
  }
  function Delete($offset)
  {
    return $this->_get_arr_value("6", $offset);
  }
  function add_Delete()
  {
    return $this->_add_arr_value("6");
  }
  function set_Delete($index, $value)
  {
    $this->_set_arr_value("6", $index, $value);
  }
  function set_all_Deletes($values)
  {
    return $this->_set_arr_values("6", $values);
  }
  function remove_last_Delete()
  {
    $this->_remove_last_arr_value("6");
  }
  function Deletes_size()
  {
    return $this->_get_arr_size("6");
  }
  function get_Deletes()
  {
    return $this->_get_value("6");
  }
}
class Response extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    self::$fields["Response"]["1"] = "Plakat";
    $this->values["1"] = array();
    self::$fieldNames["Response"]["1"] = "Plakate";
    self::$fields["Response"]["2"] = "PBInt";
    $this->values["2"] = "";
    self::$fieldNames["Response"]["2"] = "AddedCount";
    self::$fields["Response"]["3"] = "PBInt";
    $this->values["3"] = "";
    self::$fieldNames["Response"]["3"] = "ChangedCount";
    self::$fields["Response"]["4"] = "PBInt";
    $this->values["4"] = "";
    self::$fieldNames["Response"]["4"] = "DeletedCount";
  }
  function Plakate($offset)
  {
    return $this->_get_arr_value("1", $offset);
  }
  function add_Plakate()
  {
    return $this->_add_arr_value("1");
  }
  function set_Plakate($index, $value)
  {
    $this->_set_arr_value("1", $index, $value);
  }
  function set_all_Plakates($values)
  {
    return $this->_set_arr_values("1", $values);
  }
  function remove_last_Plakate()
  {
    $this->_remove_last_arr_value("1");
  }
  function Plakates_size()
  {
    return $this->_get_arr_size("1");
  }
  function get_Plakates()
  {
    return $this->_get_value("1");
  }
  function AddedCount()
  {
    return $this->_get_value("2");
  }
  function set_AddedCount($value)
  {
    return $this->_set_value("2", $value);
  }
  function ChangedCount()
  {
    return $this->_get_value("3");
  }
  function set_ChangedCount($value)
  {
    return $this->_set_value("3", $value);
  }
  function DeletedCount()
  {
    return $this->_get_value("4");
  }
  function set_DeletedCount($value)
  {
    return $this->_set_value("4", $value);
  }
}
?>