<?php
/**
	* Klassendefinitionen für Captchas
	* @author Simon Frankenberger
	* 
	* @package Captcha
	* @licence http://creativecommons.org/licenses/by-nc-sa/3.0/de/
**/

/** Konstante für alle Zahlen */
const CAPTCHA_ZAHLEN = "0123456789";

/** Konstante für alle Kleinbuchstaben */
const CAPTCHA_ALPHABET_KLEIN = "abcdefghijklmnopqrstuvxyz";

/** Konstante für alle Großbuchstaben */
const CAPTCHA_ALPHABET_GROSS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

/** Konstante für alle Hexadezimalen Zeichen */
const CAPTCHA_HEX = "ABCDEF0123456789";

/** Konstante für alle Kleinbuchstaben und Zahlen */
const CAPTCHA_ALPHABET_KLEIN_ZAHLEN = "abcdefghijklmnopqrstuvxyz0123456789";

/** Konstante für alle Großbuchstaben und Zahlen */
const CAPTCHA_ALPHABET_GROSS_ZAHLEN = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";


/** Wo sollen die Bilder gespeichert werden? */
define('CAPTCHA_BILD_PFAD', dirname(__FILE__) . '/pics/');

/** Wo sollen die Codes gespeichert werden? */
define('CAPTCHA_CODE_PFAD', dirname(__FILE__) . '/tmp/');

/** Konstante für die Standardgröße (Breite) des Bildes */
const CAPTCHA_STD_BREITE = 200;

/** Konstante für die Standardgröße (Höhe) des Bildes */
const CAPTCHA_STD_HOEHE = 60;

/** Konstante für die Standardlänge des Codes */
const CAPTCHA_STD_LAENGE = 6;

/** Konstante für die Standardzeichen des Codes */
const CAPTCHA_STD_ZEICHEN = CAPTCHA_ZAHLEN;

/** Konstante für die Standardschriftgröße */
const CAPTCHA_STD_SCHRIFT_GROESE = 22;

/** Konstante für die Standardschriftart */
const CAPTCHA_STD_SCHRIFT_DATEI = 'fonts/listen_up.ttf';

/** Wie sicher soll das Captcha sein? Größer = Besser = Langsamer */
const CAPTCHA_SICHERHEIT = 2000;

/** Wie lange soll das Captcha gültig sein? (In Sekunden) */
const CAPTCHA_GUELTIGKEIT = 300;

/** Wie sicher sollen die Rechtecke auf dem Hintergrund sein? Größer = Schlechter = Schneller */
const CAPTCHA_GROESE_RECHTECKE = 6;

/** Ist der Sicherheitscode Groß- / Kleinschreibungsabhängig? */
const CAPTCHA_SENSITIV = false;

/**
	* Klasse zur Erstellung und Prüfen von Captchas
	* 
	* @author Simon Frankenberger
	* @version 1.0.4
	* 
	* @package Captcha
	* @licence http://creativecommons.org/licenses/by-nc-sa/3.0/de/
	* 
**/
class Captcha {
	/**
		* @var string
		* @since 1.0.0
		* 
	**/
	private $code;
	
	/**
		* @var string
		* @since 1.0.0
		* 
	**/
	private $bildpfad;
	
	/**
		* @var array
		* @since 1.0.0
		* 
	**/
	private $zeichen;
	
	/**
		* @var int
		* @since 1.0.0
		* 
	**/
	private $laenge;
	
	/**
		* @var string
		* @since 1.0.0
		* 
	**/
	private $schriftart;
	
	/**
		* @var int
		* @since 1.0.0
		* 
	**/
	private $schriftgroese;
	
	/**
		* @var int
		* @since 1.0.0
		* 
	**/
	private $breite;
	
	/**
		* @var int
		* @since 1.0.0
		* 
	**/
	private $hoehe;
	
	/**
		* @var resource
		* @since 1.0.0
		* 
	**/
	private $bild;
	
	/**
		* @var string
		* @since 1.0.0
		* 
	**/
	private $dateityp;
	
	/**
		* @var boolean
		* @since 1.0.2
		* 
	**/
	private $generiert;
	
	/**
		*  Standardkonstruktor
		* 
		* @version 1.0.3
		* @since 1.0.0
		* 
	**/
	public function Captcha() {
		$this->code = '';
		$this->bildpfad = CAPTCHA_BILD_PFAD;
		$this->bild = null;
		$this->dateityp = 'jpg';
		$this->generiert = false;
		
		$this->setzeZeichen(CAPTCHA_STD_ZEICHEN);
		$this->laenge = CAPTCHA_STD_LAENGE;
		$this->schriftart = dirname(__FILE__) . '/' . CAPTCHA_STD_SCHRIFT_DATEI;
		$this->schriftgroese = CAPTCHA_STD_SCHRIFT_GROESE;
		$this->breite = CAPTCHA_STD_BREITE;
		$this->hoehe = CAPTCHA_STD_HOEHE;
		
		if(!function_exists('imagejpeg') || !function_exists('imagepng') || !function_exists('imagegif')) {
			echo '<< FEHLER >> Die Captcha-Klasse kann nicht verwendet werden, da benötigte Funktionen nicht existieren. Bitte installieren Sie die GD2-Bibliotheken für PHP.';
		}
	}
	
	/**
		* Setzt die erlaubten Zeichen für das Captcha fest
		* 
		* @version 1.0.1
		* @since 1.0.0
		* 
		* @param array|string $z Mögliche Zeichen
		* 
		* @return boolean
	**/
	public function setzeZeichen($z) {
		if(is_array($z)) {
			$this->zeichen = $z;
			
			return true;
		}
		else {
			if(is_string($z)) {
				$this->zeichen = str_split($z, 1);
				
				return true;
			}
			else {
				return false;
			}
		}
	}
	
	/**
		* Setzt den Dateityp der Ausgabe
		* 
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @param string $t Dateityp (jpg, png, gif)
		* 
		* @return boolean
	**/
	public function setzeDateityp($t) {
		switch($t) {
			case "jpeg":
			case "jpg":
				$this->dateityp = 'jpg';
				
				return true;
			case "png":
				$this->dateityp = 'png';
				
				return true;
			case "gif":
				$this->dateityp = 'gif';
				
				return true;
			default:
				return false;
		}
	}
	
	/**
		* Setzt die Schriftgröße
		* 
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @param int $g Schriftgröße
		* 
		* @return boolean
	**/
	public function setzeSchriftgroese($g) {
		if(intval($g) <= 0) {
			return false;
		}
		else {
			$this->schriftgroese = $g;
			
			return true;
		}
	}
	
	/**
		* Setzt die Breite des Bildes fest
		* 
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @param int $b Breite
		* 
		* @return boolean
	**/
	public function setzeBreite($b) {
		if(intval($b) <= 0) {
			return false;
		}
		else {
			$this->breite = $b;
			
			return true;
		}
	}
	
	/**
		* Setzt die Höhe des Bildes fest
		* 
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @param int $h Höhe
		* 
		* @return boolean
	**/
	public function setzeHoehe($h) {
		if(intval($h) <= 0) {
			return false;
		}
		else {
			$this->hoehe = $h;
			
			return true;
		}
	}
	
	/**
		* Setzt die Größe des Bildes fest
		* 
		* 
		* @see setzeBreite()
		* @see setzeHoehe()
		* 
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @param int $b Breite
		* @param int $h Höhe
		* 
		* @return boolean
	**/
	public function setzeGroese($b, $h) {
		return ($this->setzeBreite($b) && $this->setzeHoehe($h));
	}
	
	/**
		* Setzt die Länge des generierten Sicherheitscodes
		* 
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @param int $l Länge
		* 
		* @return boolean
	**/
	public function setzeLaenge($l) {
		if(intval($l) <= 0) {
			return false;
		}
		else {
			$this->laenge = $l;
			
			return true;
		}
	}
	
	/**
		* Setzt die Schriftart
		* 
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @param string $s Schriftart (Pfad, mit Dateiendung)
		* @param boolean $r Relative Pfadangabe?
		* 
		* @return boolean
	**/
	public function setzeSchriftart($s, $r=true) {
		if($r) {
			$s = dirname(__FILE__) . '/' . $s;
		}
		
		if(is_file($s) && is_readable($s)) {
			$this->schriftart = $s;
			
			return true;
		}
		else {
			if(!is_readable($s)) {
				die("Klasse Captcha: Der PHP-Daemon hat auf die angegebene Schriftart keine Leserechte!");
			}
			if(!is_dir($s)) {
				die("Klasse Captcha: Der angegebene Pfad ist keine Datei!");
			}
			
			return false;
		}
	}
	
	/**
		* Gibt den Dateinamen des Bildes zurück
		* 
		* @version 1.0.1
		* @since 1.0.0
		* 
		* @return string
	**/
	public function holeBildpfad() {
		return $this->bildpfad;
	}
	
	/**
		* Gibt den Sicherheitscode des Captchas zurück
		* 
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @return string
	**/
	public function holeCode() {
		return $this->code;
	}
	
	/**
		* Erstellt einen Seed für den Zufallsgenerator
		* 
		* @access private
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @return void
	**/
	private function zufallsgenerator_starten() {
		srand(time()+(microtime()*10000));
		
		return;
	}
	
	/**
		* Erstellt einen zufälligen Code
		* 
		* @access private
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @return void
	**/
	private function erstelle_code() {
		$this->zufallsgenerator_starten();
		
		$this->code = '';
		$anzZeichen = count($this->zeichen);
		
		for($i = 0; $i < $this->laenge; $i++) {
			$z = rand(0, $anzZeichen-1);
			
			$this->code .= $this->zeichen[$z];
		}
		
		return;
	}
	
	/**
		* Erstellt den Hintergrund des Bildes
		* 
		* @access private
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @return void
	**/
	private function erstelle_hintergrund() {
		for($i = 0; $i < CAPTCHA_SICHERHEIT; $i++) {
			$rand_x=rand(0, $this->breite);
			$rand_y=rand(0, $this->hoehe);
			
			imagefilledrectangle
			(
				$this->bild, 
				$rand_x, 
				$rand_y, 
				$rand_x+CAPTCHA_GROESE_RECHTECKE, 
				$rand_y+CAPTCHA_GROESE_RECHTECKE,
				imagecolorexact
				(
					$this->bild, 
					rand(0, 180), 
					rand(0, 180), 
					rand(0, 180) 
				)
			);
		}
		
		return;
	}
	
	/**
		* Erstellt das Objekt mit dem Bild
		* 
		* @access private
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @return void
	**/
	private function erstelle_bild() {
		$this->bild = imagecreatetruecolor($this->breite, $this->hoehe);
		
		return;
	}
	
	/**
		* Schribt die Zeichenkette auf das Bild
		* 
		* @access private
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @return void
	**/
	private function schreibe_code() {
		for($i = 0; $i < $this->laenge; $i++) {
			$aktZeichen = substr($this->code, $i, 1);
			
			imagefttext($this->bild,
									$this->schriftgroese ,
									rand(-20, 15),
									10+$i*($this->breite/$this->laenge)-rand(0, 8),
									$this->schriftgroese+15+rand(-5, 5),
									imagecolorexact($this->bild, 
																	rand(150, 255), 
																	rand(150, 255), 
																	rand(150, 255) 
																),
									$this->schriftart,
									$aktZeichen);
		}
		
		return;
	}
	
	/**
		* Schribt das Bild auf den Server
		* 
		* @access private
		* @version 1.0.1
		* @since 1.0.0
		* 
		* @return void
	**/
	private function schreibe_bild() {
		/*
			Zuerst speichern wir das Bild temporär auf dem Server
		*/
		if($this->generiert)
			$this->bildpfad = CAPTCHA_BILD_PFAD;
		
		switch($this->dateityp) {
			case 'jpg':
				imagejpeg($this->bild,
									$this->bildpfad . 'temp.jpg',
									50);
				break;
			case 'png':
				imagepng($this->bild,
								 $this->bildpfad . 'temp.png'
								);
				break;
			case 'gif':
				imagegif($this->bild,
								 $this->bildpfad . 'temp.gif'
								);
				break;
		}
		/*
			Dann lesen wir das Bild nochmal in einen String aus, und löschen es wieder
		*/
		$temp = file_get_contents($this->bildpfad . 'temp.' . $this->dateityp);
		unlink($this->bildpfad . 'temp.' . $this->dateityp);
		
		/*
			Dann generieren wir uns einen einmaligen Namen für das Bild je nach Inhalt
		*/
		$dateiname =  sha1($temp);
		$this->bildpfad .= strtolower($dateiname) . '.' . strtolower($this->dateityp);
		
		/*
			Dort schreiben wir das Bild dann final rein
		*/
		$handle = fopen($this->bildpfad, 'w');
		fwrite($handle, $temp);
		fclose($handle);
		
		/*
			Dann löschen wir alle nicht mehr benötigten Variablen
		*/
		unset($handle);
		unset($temp);
		
		/*
			Dann schreiben wir noch Infos zum Captcha auf dem Server
		*/
		$handle = fopen(CAPTCHA_CODE_PFAD . strtolower($this->code), 'w');
		fputs(
					$handle, 
					time() . "\t" . 
					$this->bildpfad . "\t" . 
					$this->code
				 );
		fclose($handle);
		
		/*
			Dann räumen wir nochmal auf
		*/
		unset($handle);
		
		return;
	}
	
	/**
		* Generiert das Captcha
		* 
		* 
		* @version 1.0.21
		* @since 1.0.0
		* 
		* @return void
	**/
	public function erstelle() {
		$this->erstelle_bild();					// Erstellt das Bildobjekt
		$this->erstelle_code();					// Erstellt den Zufallscode
		$this->erstelle_hintergrund();	// Erstellt den Hintergrund
		$this->schreibe_code();					// Schreibt den Sicherheitscode
		$this->schreibe_bild();					// Schreibt das Bild auf die Festplatte
		$this->generiert = true;				// Das Bild wurde erstellt
		
		return;
	}
	
	/**
		* Erstellt ein Formular für das generierte Captcha und gibt dieses aus
		* 
		* 
		* @version 1.0.3
		* @since 1.0.1
		* 
		* @param string $t Wohin soll das Formular geschickt werden? Std: Selbe Seite
		* @param boolean $x Soll das Formular in XHTML erstellt werden?
		* @param boolean $u Soll das Formular in UTF-8 kodiert sein?
		* 
		* @return void
	**/
	public function erstelle_formular($t='', $x = true, $u = false) {
		if(!$this->generiert) {
			return '<< FEHLER >>: Bild wurde noch nicht erstellt!';
		}
		
		if($t == '') {
			$t = $_SERVER['SCRIPT_NAME'];
		}
		
		$form = '<div style="text-align: center; width: ' . $this->breite . 'px;">' . "\n" . 
						'	<form action="' . $t . '" method="post">' . "\n" .  
						'		<input type="hidden" name="bild" value="' . basename($this->bildpfad) . '" />' . "\n" . 
						'		<img src="' . $this->bildpfad . '" width="' . $this->breite . '" height="' . $this->hoehe . '" style="margin-bottom: 5px;" /><br />' . "\n" . 
						'		<input type="text" name="code" value="" style="text-align: center; margin-bottom: 5px; width: ' . $this->breite . 'px;" maxlength="' . $this->laenge . '" /><br />' . "\n" . 
						'		<input type="submit" value="Prüfen" />' . "\n" . 
						'	</form>' . "\n" . 
						'Erlaubte Zeichen: ' . implode("", $this->zeichen) . '<br /><br />' . "\n" . 
						'</div>';
		
		if(!$x) {
			str_replace('\>', '>', $form);
		}
		
		if($u) {
			$form = utf8_encode($form);
		}
		else {
			$form = utf8_decode($form);
		}
		
		echo $form;
		
		return;
	}
	
	/**
		* Löscht abgelaufene Captchas vom Server
		* 
		* @static
		* @version 1.0.0
		* @since 1.0.0
		* 
		* @return void
	**/
	public static function Aufraeumen() {
		$handle_v = opendir(CAPTCHA_CODE_PFAD);
		
		while($datei = readdir($handle_v)) {
			if(substr($datei, 0, 1) == '.')
				continue;
			
			$handle_d = fopen(CAPTCHA_CODE_PFAD . $datei, 'r');
			$temp = explode("\t", fread($handle_d, 1024));
			fclose($handle_d);
			
			if($temp[0] + CAPTCHA_GUELTIGKEIT < time()) {
				unlink(CAPTCHA_CODE_PFAD . $datei);
				unlink($temp[1]);
			}
		}
		
		return;
	}
	
	/**
		* Überprüft, ob das Bild und der Code zusammenpassen
		* 
		* @static
		* 
		* @see Aufraeumen()
		* 
		* @param string $c Der zu überprüfende Code
		* @param string $b Der Pfad zum angezeigten Bild
		* 
		* @version 1.0.3
		* @since 1.0.0
		* 
		* @return boolean
	**/
	public static function Ueberpruefen($c, $b) {
		Captcha::Aufraeumen();
		$code = basename($c);
		$bild = strtolower(basename($b));
		
		if($c == "" || $b == "") {
			return false;
		}
		
		if(!file_exists(CAPTCHA_CODE_PFAD . $code) || !file_exists(CAPTCHA_BILD_PFAD . $bild)) {
			return false;
		}
		
		$handle_d = fopen(CAPTCHA_CODE_PFAD . $code, 'r');
		$temp = explode("\t", fread($handle_d, 1024));
		fclose($handle_d);
		
		if(CAPTCHA_SENSITIV) {
			$vergleich = (strcasecmp($temp[2], $code) == 0);
		}
		else {
			$temp[2] = strtolower($temp[2]);
			$code = strtolower($code);
			$vergleich = ($temp[2] == $code);
		}
		
		if($bild === basename($temp[1]) && $vergleich) {
			unlink(CAPTCHA_CODE_PFAD . $code);
			unlink(CAPTCHA_BILD_PFAD . $bild);
			return true;
		}
		else {
			return false;
		}
	}
}

Captcha::Aufraeumen();
?>