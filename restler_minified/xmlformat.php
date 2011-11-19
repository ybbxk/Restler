<?php
 class XmlFormat implements iFormat { public static $parse_attributes=true; public static $parse_namespaces=false; public static $attribute_names=array('xmlns'); public static $root_name='response'; public static $default_tag_name='item'; const MIME ='application/xml'; const EXTENSION = 'xml'; public function getMIMEMap() { return array(XmlFormat::EXTENSION=>XmlFormat::MIME); } public function getMIME(){ return XmlFormat::MIME; } public function getExtension(){ return XmlFormat::EXTENSION; } public function setMIME($mime){ } public function setExtension($extension){ } public function encode($data, $human_readable=false){ return $this->toXML( object_to_array($data, false), XmlFormat::$root_name, $human_readable); } public function decode($data){ try { if($data=='')return array(); return $this->toArray($data); } catch (Exception $e) { throw new RestException(400, "Error decoding request. ". $e->getMessage()); } } public function __toString(){ return $this->getExtension(); } public function isAssoc( $array ) { return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array))))); } public function toXML( $data, $root_node_name = 'result', $human_readable=false, &$xml=null) { if (ini_get('zend.ze1_compatibility_mode') == 1) ini_set ('zend.ze1_compatibility_mode', 0); if (is_null($xml)) $xml = @simplexml_load_string("<$root_node_name/>"); if(is_array($data)){ $numeric=0; foreach( $data as $key => $value ) { if ( is_numeric( $key ) ) { $numeric = 1; $key = XmlFormat::$root_name == $root_node_name ? XmlFormat::$default_tag_name : $root_node_name; } $key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key); if ( is_array( $value ) ) { $node = $this->isAssoc( $value ) || $numeric ? $xml->addChild( $key ) : $xml; if ( $numeric ) $key = 'anon'; $this->toXML($value, $key, $human_readable, $node); } else { in_array($key,XmlFormat::$attribute_names) ? $xml->addAttribute($key,$value) : $xml->addChild( $key, $value); } } }else{ if(is_bool($data)) $data = $data ? 'true' : 'false'; $xml = @simplexml_load_string( "<$root_node_name>$data</$root_node_name>"); } if(!$human_readable){ return $xml->asXML(); }else{ $dom = dom_import_simplexml($xml)->ownerDocument; $dom->formatOutput = true; return $dom->saveXML(); } } public function toArray( $xml, $firstCall=true) { if ( is_string( $xml ) ) $xml = new SimpleXMLElement( $xml ); $children = $xml->children(); if ( !$children ) { $r = (string) $xml; if($r=='true' || $r=='false')$r=$r=='true'; return $r; } $arr = array(); if($firstCall){ XmlFormat::$attribute_names=array(); XmlFormat::$root_name = $xml->getName(); if (XmlFormat::$parse_namespaces){ foreach($xml->getDocNamespaces(TRUE) as $namepace => $uri) { $arr[$namepace=='' ? 'xmlns' : 'xmlns:'.$namepace] = (string)$uri; } } } if(XmlFormat::$parse_attributes){ foreach($xml->attributes() as $attName => $attValue) { $arr[$attName] = (string)$attValue; XmlFormat::$attribute_names[]=$attName; } } foreach ($children as $key => $node) { $node = $this->toArray($node, false); if ($key == 'anon') $key = count($arr); if (isset($arr[$key])) { if ( !is_array($arr[$key]) || @$arr[$key][0] == null ) $arr[$key] = array($arr[$key]); $arr[$key][] = $node; } else { $arr[$key] = $node; } } return $arr; } public static function exportCurrentSettings() { $s = 'XmlFormat::$root_name = "'. (XmlFormat::$root_name)."\";\n"; $s .= 'XmlFormat::$attribute_names = '. (var_export(XmlFormat::$attribute_names, true)).";\n"; $s .= 'XmlFormat::$default_tag_name = "'. XmlFormat::$default_tag_name."\";\n"; $s .= 'XmlFormat::$parse_attributes = '. (XmlFormat::$parse_attributes ? 'true' : 'false').";\n"; $s .= 'XmlFormat::$parse_namespaces = '. (XmlFormat::$parse_namespaces ? 'true' : 'false').";\n\n\n"; return $s; } } 