<?php
#TODO tratamento do undefined (mac que não está na lista)
defined('BASEPATH') OR exit('No direct script access allowed');
class Ext_configs extends CI_Model {

	var $gsconfig = array();
	var $groups = 'undefined';

	
	public function xmlconfig($mac)
	{
		$this->cfg_load_ext($mac);

		$xml = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><gs_provision/>");
		$xml->addAttribute('version', '1');
		$xml->addChild("mac", $mac);
		$xml->addChild("config")
			->addAttribute('version', '1');
		

		foreach ($this->gsconfig as $k => $v)
		{
		  $xml->config->addChild($k, $v);
		}

		return $xml->saveXML();
	}

	private function cfg_load_group($group)
	{
		if ( $file = fopen("data/".$group.".txt","r") )
		{
			while(! feof($file))
			{
				$line = fgets($file);
				$uline = explode('#', $line);

				$pv = explode('=', $uline[0]);

				# Filtra linhas com comentários
				if ( count($pv) < 2 )
					continue;

				$this->gsconfig[trim($pv[0])] = trim($pv[1]);
			}

			fclose($file);
		}

		else
			throw new Exception("Erro no ".$group.".txt", 1);
	}

	private function cfg_load_groups($groups)
	{
		$vgroups = explode('/', $groups);

		foreach (array_reverse($vgroups) as $group) {
			$this->cfg_load_group($group);
		}
	}

	private function cfg_load_ext($mac)
	{
		if ( $file = fopen("data/extensions.txt","r") )
		{
			while(! feof($file))
			{
				$line = fgets($file);
				$uline = explode('#', $line);

				# Procura os groupos
				if ( preg_match('/\[\s*((?>\w+\/?)+)\s*\]/', $uline[0], $match) )
				{
					$groups = trim( strtolower($match[1]), '/');
					continue;
				}

				$fields = explode(';', $uline[0]);

				# Filtra linhas com comentários
				if ( count($fields) < 2 )
					continue;

				if ( strtolower(trim($fields[0])) == $mac )
				{
					array_shift($fields);
					
					$this->cfg_load_groups($groups);

					$this->groups = $groups;

					foreach ($fields as $value)
					{
						$pv = explode('=', $value);
						$this->gsconfig[trim($pv[0])] = trim($pv[1]);
					}
					break;
				}
			}

			fclose($file);
		}
		else
			throw new Exception("Erro no extensions.txt", 1);
	}

}
