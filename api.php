<?php
/*
	@author: NIKET MALIK <niketmalik@gmail.com>
			 https://google.com/+NiketMalik
	@author(torrent trader): tornav
	@name: TT API v1
	@TT version: 2.08
	@TT theme: default
	@Mod version: 1.0.0
	@lisence: released under the MIT license
*/

/*
	@return
		@status - int - HTTP Code
		@message - string - HTTP Code Message
		@data - void - Array of torrent
			@hash - string - hash of torrent
			@name - string - name of torrent
			@description - string - description of torrent
			@image1 - string - url of image 1 - null if empty
			@image2 - string - url of image 2 - null if empty
			@category - string - category of torrent
			@files - int - number of files in the torrent
			@leechers - int - number of leechers
			@seeders - int - number of seeders
			@completed - int - number of completed downloads
			@nfo - string - base64 encoded nfo file - null if empty

*/

require_once("backend/functions.php");

class ttAPI {

	protected $_request = array();

	public function __construct() {
		if($GLOBALS['CURUSER']["view_torrents"]==="no")
				$this->doComplete(array('status'=>403,'message'=>"Forbidden :: Administrator has turned `view_torrents` off."), TRUE);

		$this->site_config = $GLOBALS['site_config'];

		$this->inputs();
		$this->init();
	}

	protected function init() {

		dbconn();

		if($this->_request["count"]===NULL)
			$count = 25;
		else
			$count = $this->_request["count"];

		$host = parse_url($this->site_config['SITEURL'], PHP_URL_HOST);

		$res = SQL_Query_exec("SELECT * FROM categories");
		while($row = mysqli_fetch_assoc($res)) {
			$cats[$row['sub_sort']] = $row['parent_cat'] . "::" . $row["name"];
		}

		$res = SQL_Query_exec("SELECT 
								`info_hash` as `hash`, 
								`name`, 
								`descr` as `description`, 
								`image1`, 
								`image2`, 
								`category`, 
								`numfiles` as `files`, 
								`leechers`, 
								`seeders`,
								`times_completed` as `completed`, 
								`nfo`, 
								`banned`, 
								`visible`, 
								`id` 
							FROM torrents
							ORDER BY `id` DESC
							LIMIT " . $count);

		while($row = mysqli_fetch_assoc($res)) {
			$row["name"] = "[" . $host . "]" . $row["name"];
			$row["image1"] = (!empty($row["image1"])) ?  "//" . $host . "/uploads/images/" . $row["image1"] : NULL;
			$row["image2"] = (!empty($row["image2"])) ?  "//" . $host . "/uploads/images/" . $row["image2"] : NULL;
			$row["category"] = $cats[$row["category"]];
			$row["nfo"] = ($row["nfo"]!=="no") ? base64_encode(file_get_contents($this->site_config['nfo_dir'] . "/" . $row['id'] . ".nfo")) : NULL;
			unset($row[id]);
			if($row['banned']==="no" && $row['visible']==="yes") {
				unset($row['banned']);
				unset($row['visible']);
				$torrents[] = $row;
			}
		}

		echo $this->doComplete(array('status'=>200,'message'=>$this->protocol(200),'data'=>$torrents));

	}

	protected function protocol($code) {

		$protocol = array(
						100 => 'Continue',  
						101 => 'Switching Protocols',  
						200 => 'OK',
						201 => 'Created',  
						202 => 'Accepted',  
						203 => 'Non-Authoritative Information',  
						204 => 'No Content',  
						205 => 'Reset Content',  
						206 => 'Partial Content',  
						300 => 'Multiple Choices',  
						301 => 'Moved Permanently',  
						302 => 'Found',  
						303 => 'See Other',  
						304 => 'Not Modified',  
						305 => 'Use Proxy',  
						306 => '(Unused)',  
						307 => 'Temporary Redirect',  
						400 => 'Bad Request',  
						401 => 'Unauthorized',  
						402 => 'Payment Required',  
						403 => 'Forbidden',  
						404 => 'Not Found',  
						405 => 'Method Not Allowed',  
						406 => 'Not Acceptable HOST Or IP, See Ban List.',  
						407 => 'Proxy Authentication Required',  
						408 => 'Request Timeout',  
						409 => 'Conflict',  
						410 => 'Gone',  
						411 => 'Length Required',  
						412 => 'Precondition Failed',  
						413 => 'Request Entity Too Large',  
						414 => 'Request-URI Too Long',  
						415 => 'Unsupported Media Type',  
						416 => 'Requested Range Not Satisfiable',  
						417 => 'Expectation Failed', 
						428 => 'Origin Error',
						500 => 'Internal Server Error',  
						501 => 'Not Implemented',  
						502 => 'Bad Gateway',  
						503 => 'Service Unavailable',  
						504 => 'Gateway Timeout',  
						505 => 'HTTP Version Not Supported'
		);
	
		return $protocol[$code];
	
	}

	protected function inputs() {
  
		switch($_SERVER['REQUEST_METHOD']) {
			case "GET":
				$this->_request = $this->_cleanInputs($_GET);
				break;
			default:
				$this->doComplete(array('status'=>405,'message'=>$this->protocol(405)), TRUE);
				exit;
				break;
		}
			
	}	
  
	private function _cleanInputs($data) {
		$args = array(
				'count' => array(
                             'filter' => FILTER_VALIDATE_REGEXP,
                             'options' => array('regexp' => '/^[0-9]{1,3}$/')
                             )
		);
    
		$filter = filter_var_array($data,$args);
		
		return $filter;
    
	}

	public function doComplete($data, $die = FALSE) {
    
		header("HTTP/1.1 " . $data['status'] . " " . $data['message']);
		header("Content-Type: application/json");
    
		$this->db = NULL;
    
		if(TRUE === $die) {
			echo json_encode($data);
			exit;
		} else {
			return json_encode($data);
		}
    
	}


}

new ttAPI();

?>