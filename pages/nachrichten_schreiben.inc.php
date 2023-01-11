<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Nachrichten');

$reply = getOrDefault($_GET, 'reply', 0);

$receiver = null;
$subject = null;
$message = null;
if ($reply > 0) {
    $data = Database::getInstance()->getMessageByIdAndAnOrVonEquals($reply, $_SESSION['blm_user']);
    requireEntryFound($data, '/?p=nachrichten_liste');

    $receiver = $data['VonName'];
    $subject = stripos($data['Betreff'], 'Re:') === false ? 'Re: ' . $data['Betreff'] : $data['Betreff'];
    $message = "\n\n[quote]" . $data['Nachricht'] . "[/quote]";
}

$receiver = getOrDefault($_GET, 'receiver', $receiver);
$subject = getOrDefault($_GET, 'subject', $subject);
$message = getOrDefault($_GET, 'message', $message);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/messenger.webp" alt=""/>
    <span>Nachricht schreiben<?= createHelpLink(1, 13); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div class="form NachrichtSchreiben">
    <form action="/actions/nachrichten.php?a=1" method="post">
        <input type="hidden" name="broadcast" id="broadcast" value="0"/>
        <header>Nachricht</header>
        <div>
            <label for="receiver">Empfänger</label>
            <input type="text" name="receiver" id="receiver" value="<?= escapeForOutput($receiver); ?>"/>
            <?= (isAdmin() ? '<a href="#" onclick="return toggleRundmail();" id="toggle_rundmail">Admin Rundmail</a>' : ''); ?>
        </div>
        <div>
            <label for="subject">Betreff</label>
            <input type="text" name="subject" id="subject" value="<?= escapeForOutput($subject); ?>"/>
        </div>
        <div>
            <label for="message">Nachricht</label>
            <textarea id="message" name="message" maxlength="4096" cols="60" rows="20"
                      onkeyup="ZeichenUebrig(this, document.getElementById('charsLeft'));"><?= escapeForOutput($message, false); ?></textarea>
        </div>
        <div>
            Noch <span id="charsLeft">4096</span> Zeichen übrig.
            <input type="submit" value="Absenden" id="send_message"/>
        </div>
    </form>
</div>

<div>
    <?php
    if ($reply !== 0) {
        echo sprintf('<a href="/?p=nachrichten_lesen&amp;id=%d">&lt;&lt; Zurück</a>', $reply);
    } else {
        echo '<a href="/?p=nachrichten_liste">&lt;&lt; Zurück</a>';
    }
    ?>

</div>

<script>
    ZeichenUebrig(document.getElementById('message'), document.getElementById('charsLeft'));
</script>
