<?php
	/**
		* Wird in die index.php eingebunden; Seite zum Anmelden am Spiel
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.pages
	*/
?>
<table id="SeitenUeberschrift">
	<tr>
		<td style="width: 80px;"><img src="pics/big/login.png" alt="Logo der Unterseite" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Login</td>
	</tr>
</table>

<?=$m; ?>

<b>
	Hier k&ouml;nnen Sie sich im Spiel anmelden. Geben Sie hierzu Ihre Logindaten, mit denen Sie sich registriert haben, ein.
</b>
<br />
<br />
<form action="./actions/login.php" method="post">
	<table class="Liste" style="width: 250px" cellspacing="0">
		<tr>
			<th colspan="2">Login f&uuml;r registrierte Benutzer</th>
		</tr>
		<tr>
			<td style="width: 90px; text-align: right;">Benutzername:</td>
			<td><input name="name" type="text" size="15" /></td>
		</tr>
		<tr>
			<td style="width: 90px; text-align: right;">Passwort:</td>
			<td><input name="pwd" type="password" size="15" /></td>
		</tr>
		<tr>
			<td style="text-align: center;" colspan="2">
			<?php
				if(CheckGameLock()) {
					echo '<input type="submit" value="Anmelden" /><br /><span class="MeldungR">Das Spiel ist zur Zeit pausiert, Anmeldungen sind erst wieder bei Rundenstart möglich.</span>';
				}
				else {
					echo '<input type="submit" value="Anmelden" />';
				}
			?>
			</td>
		</tr>
	</table>
</form>