<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gs extends CI_Controller {

	var $gsconfig = array();

	public function index()
	{
		echo "string";
	}

	public function req_file($filename)
	{
		if ( preg_match('/cfg([0-f]+)\.xml/', $filename, $match) )
		{
			$mac = $match[1];
			$this->xmlconfig($mac);
			return;
		}

		if ( preg_match('/gxp1600fw\.bin/', $filename, $match) )
		{
			$this->output
				->set_content_type('application/octet-stream')
				->set_output(file_get_contents('data/fw/gxp1600fw.bin'));

			return;
		}
		
		show_404();
	
	}

	private function xmlconfig($mac)
	{
		$this->gsgeral();
		$this->gsuser($mac);

		$xml = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><gs_provision/>");
		$xml->addAttribute('version', '1');
		$xml->addChild("mac", $mac);
		$xml->addChild("config")
			->addAttribute('version', '1');
		

		foreach ($this->gsconfig as $k => $v)
		{
		  $xml->config->addChild($k, $v);
		}

		$this->output
			->set_content_type('application/xml')
			->set_output($xml->saveXML());
	}

	private function gsgeral()
	{
		if ( $file = fopen("data/global.txt","r") )
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
			throw new Exception("Erro no global.txt", 1);
	}

	private function gsuser($mac)
	{
		if ( $file = fopen("data/extensions.txt","r") )
		{
			while(! feof($file))
			{
				$line = fgets($file);
				$uline = explode('#', $line);
				$fields = explode(';', $uline[0]);

				# Filtra linhas com comentários
				if ( count($fields) < 2 )
					continue;
				
				if ( trim($fields[0]) == $mac )
				{
					array_shift($fields);
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
