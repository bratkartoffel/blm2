<?php
	/**
		* Wird in die index.php eingebunden; Formular zum Verfassen einer neuen Nachricht.
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.pages
	*/
?><table id="SeitenUeberschrift">
	<tr>
		<td style="width: 80px;"><img src="pics/big/writemail.png" alt="Nachricht verfassen" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Neue Nachricht verfassen
			<a href="./?p=hilfe&amp;mod=1&amp;cat=13"><img src="pics/help.gif" alt="Hilfe" style="border: none;" /></a>
		</td>
	</tr>
</table>
<?php
	if(!$ich->Sitter->Nachrichten && $_SESSION['blm_sitter']) {
		echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
	}
	else {
		if($_SESSION['blm_admin'] == "1" && $_GET['admin_vorlage'] != "") {
			$text = $vorlage_admin[intval($_GET['admin_vorlage'])];
		}
?> 

<?=$m; ?>

<form action="./actions/nachrichten.php?a=1" method="post" name="form_message">
	<table class="Liste" style="width: 600px; margin-top: 20px;" cellspacing="0">
		<tr>
			<th style="border:none; border-right: solid 1px darkred;">Empf&auml;nger:</th>
			<td>
				Benutzer:
				<select name="an" style="min-width: 150px;">
					<option value="">- Bitte auswählen -</option>
					<option disabled="disabled">========</option>
					<?php
						$sql_abfrage="SELECT
														ID,
														Name
													FROM
														mitglieder
													WHERE
														ID<>'" . $_SESSION['blm_user'] . "'
													AND
														ID>0
													ORDER BY
														Name;";
						$sql_ergebnis=mysql_query($sql_abfrage);
						$_SESSION['blm_queries']++;
						
						while($user=mysql_fetch_object($sql_ergebnis)) {
							if($user->ID==intval($_GET['an'])) {
								echo '<option selected="selected" value="' . $user->ID . '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</option>';
							}
							else {
								echo '<option value="' . $user->ID . '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</option>';
							}
						}
						
						if(IstAdmin() || IstBetatester()) {
							echo '<option disabled="disabled">========</option>';
							echo '<option value="1337">Rundmail</option>';
						}
						
						$sql_abfrage="SELECT
														n.*,
														m.Name AS Empfaenger
													FROM
														nachrichten n LEFT OUTER JOIN mitglieder m ON n.Von=m.ID
													WHERE
														n.ID='" . intval($_GET['answer']) . "'
													AND
														n.An='" . $_SESSION['blm_user'] . "';";
						$sql_ergebnis=mysql_query($sql_abfrage);
						$_SESSION['blm_queries']++;
						
						$antwort=mysql_fetch_object($sql_ergebnis);
						
						if($antwort->Nachricht != "") {
							$sql_abfrage="UPDATE
															nachrichten
														SET
															Gelesen=1
														WHERE
															ID='" . intval($_GET['answer']) . "';";
							mysql_query($sql_abfrage);
							$_SESSION['blm_queries']++;
							
							$sql_abfrage="UPDATE
															log_nachrichten
														SET
															Gelesen=1
														WHERE
															Orig_ID='" . intval($_GET['answer']) . "'
														;";
							mysql_query($sql_abfrage);
							$_SESSION['blm_queries']++;
						}
					?>
				</select>
				oder Admin:
				<select name="admin" style="min-width: 150px;">
					<option value="">- Bitte auswählen -</option>
					<option disabled="disabled">========</option>
<?php
	$sql_abfrage="SELECT
									ID,
									Name
								FROM
									mitglieder
								WHERE
									Admin = '1'
								AND
									ID > 0
								AND
									ID != '" . $_SESSION['blm_user'] . "'
								ORDER BY
									Name DESC;";
	$sql_ergebnis=mysql_query($sql_abfrage);
	
	while($admin = mysql_fetch_object($sql_ergebnis)) {
?>
					<option value="<?=$admin->ID; ?>"><?=sichere_ausgabe($admin->Name); ?></option>
<?php
	}
?>
				</select>
			</td>
		</tr>
		<tr>
			<th style="border:none; border-right: solid 1px darkred;">Betreff:</th>
			<td><input type="text" name="betreff" value="<?php
				if($antwort->Betreff!="") {
					echo "RE: " . stripslashes(htmlentities($antwort->Betreff, ENT_QUOTES, "UTF-8"));
				}
				
				if($_GET['betreff'] != "") {
					echo stripslashes(htmlentities($_GET['betreff'], ENT_QUOTES, "UTF-8"));
				}
			?>" maxlength="30" size="30" /></td>
		</tr>
		<tr>
			<th style="border:none; border-right: solid 1px darkred;">
				Nachricht:<br />
				<a href="popups/smiley.php" onclick="return SmileyPopupZeigen(this.href);">Emoticons</a><br />
				<a href="popups/bbcode.php" onclick="return BBCodePopupZeigen(this.href);">BB-Code</a>
			</th>
			<td><textarea name="nachricht" cols="80" rows="15" maxlength="4096" onkeyup="ZeichenUebrig(this, document.form_message.getElementsByTagName('span')[0]);"><?php
				if($antwort->Nachricht!="") {
					echo "[quote][b][i]" . stripslashes(htmlentities($antwort->Empfaenger, ENT_QUOTES, "UTF-8")) . " hat am " . date("d.m.Y", $antwort->Zeit) . " um " . date("H:i", $antwort->Zeit) . " geschrieben:[/i][/b]\n" . stripslashes(htmlentities($antwort->Nachricht, ENT_QUOTES, "UTF-8")) . "[/quote]\n";
				}
				
				if($_GET['nachricht'] != "") {
					echo stripslashes(htmlentities($_GET['nachricht'], ENT_QUOTES, "UTF-8"));
				}
				
				if($text) {
					$name = getSpielerName($_SESSION['blm_user']);
					$text = str_replace('__NAME__', $name, $text);
					
					if($name == "Bratkartoffel")
						$text = preg_replace('/\,\ im\ Auftrag\ von.*/im', '', $text);
				}
				
				echo $text;
				
				?></textarea></td>
		</tr>
		<tr>
			<th style="text-align: center; padding: 3px; font-size: 80%; background-color: #b0ee7b; border-top: solid 1px #aa0000; border-right: solid 1px #aa0000;  border-bottom: none; font-weight: normal;">Noch <span>4096</span> Zeichen verbleibend.</th>
			<th style="text-align: center; padding: 3px; border-top: solid 1px #aa0000; border-bottom: none;"><input type="submit" value="Nachricht versenden" onclick="document.forms[0].submit(); this.disabled='disabled'; this.value='Bitte warten...'; smileyPopup.close(); BBCodePopup.close(); return false;" /></th>
		</tr>
	</table>
</form>
<script type="text/javascript">
	<!--
		ZeichenUebrig(document.form_message.nachricht, document.form_message.getElementsByTagName('span')[0]);
	// -->
</script>
<?php
	}
?>