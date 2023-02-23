/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

function ChefboxZeigen() {
    const popup = window.open('chefbox.php', 'chefbox', 'height=800,width=450,scrollbars=yes,resizable=yes');
    popup.focus();
    return false;
}

function BLMEnde() {
    if (opener) {
        opener.focus();
        self.close();
    } else {
        document.location.href = '/actions/logout.php?popup=1';
    }
    return false;
}

function BLMNavigation(link) {
    if (opener) {
        opener.document.location.href = link;
        opener.focus();
    } else {
        let blm2 = window.open(link);
        opener = blm2;
        blm2.focus();
    }
    return false;
}

function ZeichenUebrig(Feld, Text) {
    Text.innerText = 4096 - Feld.value.length;
    return true;
}

// used in plantage.inc.php
function RechneProduktionsKosten(BasisMenge, BasisPreis, Menge, Geld, TextFeld, Button) {
    const kosten = Menge * (BasisPreis / BasisMenge);

    TextFeld.innerText = 'Kosten: ' + kosten.toLocaleString(navigator.language, {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    }) + ' €';

    if (kosten > Geld || kosten < 0 || isNaN(kosten)) {
        Button.enabled = '';
        Button.disabled = 'disabled';
    } else {
        Button.disabled = '';
        Button.enabled = 'enabled';
    }
}

// used in gruppe_diplomatie.inc.php
function CheckKrieg(e) {
    if (e.selectedIndex === 2) {
        document.getElementById('kriegBetrag').style.display = 'block';
    } else {
        document.getElementById('kriegBetrag').style.display = 'none';
    }
}

function confirmAbort(kosten, percentReturn) {
    return confirm('Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen nur ' + percentReturn + ' (entspricht ' + kosten + ') der Kosten zurückerstattet!');
}

function CountdownFields() {
    let countdown = (field, direction) => {
        let value;
        if (!field.innerText.includes('Tage')) {
            value = Date.parse('1970-01-01T' + field.innerText + 'Z');
        } else {
            let days = field.innerText.split(' Tage ')[0];
            let hours = field.innerText.split(' Tage ')[1];
            value = Date.parse('1970-01-01T' + hours + 'Z') + (1000 * 86400 * days);
        }
        if (value > 0 || direction > 0) {
            field.innerText = '';
            let date = new Date(value + direction * 1000);
            if (value > 86400000 && direction < 0) {
                field.innerText += Math.floor(value / 86400000) + ' Tage';
            }
            field.innerText += ' ' + date.toLocaleTimeString('de-DE', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'UTC'
            });
            if (field.innerText === '00:00:00' && reloadOnCountdown) {
                if (document.location.href.includes('?')) {
                    document.location.href = document.location.href + '&rld=1';
                } else {
                    document.location.href = document.location.href + '?rld=1';
                }
            }
        }
    };
    Array.prototype.forEach.call(document.getElementsByClassName('countdown'), field => countdown(field, -1))
    Array.prototype.forEach.call(document.getElementsByClassName('countup'), field => countdown(field, 1))
}

// used in mafia.inc.php
function MafiaActionChange() {
    let action = Number.parseInt(document.getElementById('mafia_action').value);
    let data = mafia_cost_data[action];
    let texts = [
        data['costs'][0] + ' € / ' + (100 * data['chance'][0]) + '%',
        data['costs'][1] + ' € / ' + (100 * data['chance'][1]) + '%',
        data['costs'][2] + ' € / ' + (100 * data['chance'][2]) + '%',
        data['costs'][3] + ' € / ' + (100 * data['chance'][3]) + '%',
    ];

    let options = document.getElementById('level').getElementsByTagName('option');
    for (let i = 0; i < options.length; i++) {
        options[i].innerText = texts[i];
    }
}

// used in bank.inc.php
function ChangeBankDepositWithdraw() {
    // noinspection JSUnresolvedVariable
    const form = document.form_bank;
    // noinspection JSUnresolvedVariable
    const option = form.art.value;
    // noinspection JSUnresolvedVariable
    const field = form.betrag;
    const bank = field.getAttribute('data-bank');
    const hand = field.getAttribute('data-geld');
    const maxDeposit = Math.min(hand, field.getAttribute('data-deposit-limit') - bank).toFixed(2);
    const maxWithdraw = Math.max(0, bank).toFixed(2);
    const currentValue = Number.parseFloat(field.value).toFixed(2);
    // only change value if the user didn't change it yet
    if (currentValue === '0.00'
        || currentValue === maxDeposit
        || currentValue === maxWithdraw
        || currentValue === hand.toFixed(2)
    ) {
        switch (option) {
            case "1": // einzahlen
                field.value = maxDeposit;
                break;
            case "2": // auszahlen
                field.value = maxWithdraw;
                break;
            case "3": // gruppenkasse
                field.value = hand;
                break;
        }
    }
}

// used in nachrichten_schreiben.inc.php
function toggleRundmail() {
    let f = document.getElementById('receiver');
    let b = document.getElementById('broadcast');
    if (b.value === '0') {
        f.value = 'RUNDMAIL';
        f.disabled = 'disabled';
        b.value = '1';
    } else {
        f.value = '';
        f.disabled = '';
        f.enabled = 'enabled';
        b.value = '0';
    }
    return false;
}

// used in einstellungen.inc.php
function enableSitterOptions(enabled) {
    Array.prototype.forEach.call(document.getElementById('sitterSettings').getElementsByTagName('input'), (field) => {
        if (field === enableSitting || field.type === 'submit' || field.type === 'hidden') return;
        field.disabled = !enabled;
    });
}

// used in chefbox.php
function chefboxPollJobs() {
    if (opener) {
        window.setInterval(() => {
            let messages = opener.document.getElementsByClassName('MessageBox');
            if (messages.length !== 0) {
                let message = messages[0];
                if (message.hasAttribute('reload-chefbox')) {
                    message.removeAttribute('reload-chefbox');
                    window.location.reload();
                }
            }
        }, 1000);
    }
}

/* de-obfuscate fields with personal information */
function deobfuscate() {
    let fields = document.getElementsByClassName('bot');
    for (let i = 0; i < fields.length; i++) {
        let botField = fields.item(i);
        let obf;
        let domain = '';
        if (botField.textContent.indexOf('@') !== -1) {
            obf = botField.textContent.substring(0, botField.textContent.indexOf('@'));
            domain = botField.textContent.substring(botField.textContent.indexOf('@'));
        } else {
            obf = botField.textContent;
        }
        let deobf = obf.match(/.{1,2}/g).map(v => String.fromCharCode(parseInt(v, 16))).join('');
        botField.textContent = deobf + domain;
    }
}

// reload the page when a countdown reaches 0
let reloadOnCountdown = false;

// deobfuscate all fields when this script is loaded
deobfuscate();

// start the countdown
window.setInterval(CountdownFields, 1000);

// used for gruppe_diplomatie.inc.php
function confirmGruppeDiplomatie() {
    let typElement = document.getElementById('relation_typ');
    if (typElement !== null) {
        typElement.onchange = () => CheckKrieg(typElement);
        CheckKrieg(typElement);
    }

    for (let cancelLink of document.getElementsByClassName('cancel_relation')) {
        let type = cancelLink.getAttribute('data-type');
        let partner = cancelLink.getAttribute('data-partner');
        cancelLink.onclick = () => confirm('Wollen Sie die ' + type + ' Beziehung mit "' + partner + '" wirklich kündigen?');
    }

    for (let retractLink of document.getElementsByClassName('retract_relation_offer')) {
        let type = retractLink.getAttribute('data-type');
        let partner = retractLink.getAttribute('data-partner');
        retractLink.onclick = () => confirm('Wollen Sie die ' + type + ' Anfrage mit "' + partner + '" wirklich zurückziehen?');
    }

    for (let surrenderLink of document.getElementsByClassName('war_surrender')) {
        let name = surrenderLink.getAttribute('data-name');
        let amount = surrenderLink.getAttribute('data-amount');
        let points = surrenderLink.getAttribute('data-points');
        let plantage = surrenderLink.getAttribute('data-plantage');
        surrenderLink.onclick = () => confirm('Wollen Sie in dem Krieg mit ' + name
            + ' wirklich kapitulieren? Der umkämpfte Betrag (' + amount
            + ') geht an den Gegner, jeder Ihrer Gruppenmitglieder verliert ' + points
            + ' seiner Punkte und ' + plantage + ' Stufe(n) seiner Plantagen!');
    }
}

confirmGruppeDiplomatie();

// used for marktplatz_liste.inc.php
function confirmMarktplatzListe() {
    for (let buyLink of document.getElementsByClassName('market_buy_offer')) {
        let number = buyLink.getAttribute('data-id');
        buyLink.onclick = () => confirm('Wollen Sie das Angebot Nr ' + number + ' wirklich kaufen?');
    }
    for (let retractLink of document.getElementsByClassName('market_retract_offer')) {
        let number = retractLink.getAttribute('data-id');
        let refund = retractLink.getAttribute('data-refund');
        retractLink.onclick = () => confirm('Wollen Sie das Angebot Nr ' + number
            + ' wirklich zurückziehen?\nSie erhalten lediglich ' + refund
            + ' kg der Waren zurück.');
    }
}

confirmMarktplatzListe();

// used for admin_benutzer.inc.php
function confirmAdminBenutzer() {
    for (let deleteLink of document.getElementsByClassName('delete_user')) {
        deleteLink.onclick = () => confirm('Benutzer "' + deleteLink.getAttribute('data-username') + '"wirklich löschen?');
    }
}

confirmAdminBenutzer();

// used for marktplatz_gruppe.inc.php
function confirmAdminGruppe() {
    for (let deleteLink of document.getElementsByClassName('delete_group')) {
        deleteLink.onclick = () => confirm('Gruppe "' + deleteLink.getAttribute('data-groupname') + '"wirklich löschen?');
    }
}

confirmAdminGruppe();

// used for forschungszentrum.inc.php and gebaeude.inc.php
function confirmJobAbort() {
    for (let deleteLink of document.getElementsByClassName('delete_job')) {
        deleteLink.onclick = () => confirmAbort(deleteLink.getAttribute('data-refund'), deleteLink.getAttribute('data-percent'));
    }
}

confirmJobAbort();

// used for gruppe_mitgliederverwaltung.inc.php
function confirmGruppeMitglieder() {
    for (let kickLink of document.getElementsByClassName('kick_member')) {
        let username = kickLink.getAttribute('data-username');
        kickLink.onclick = () => confirm('Wollen Sie das Mitglied "' + username + '" wirklich aus der Gruppe entfernen?');
    }
}

confirmGruppeMitglieder();

// used for vertraege_liste.inc.php
function confirmVertraegeListe() {
    // require confirmation when accepting contract
    for (let acceptLink of document.getElementsByClassName('accept_contract')) {
        let number = acceptLink.getAttribute('data-id');
        acceptLink.onclick = () => confirm('Wollen Sie den Vertrag Nr ' + number + ' wirklich annehmen?');
    }
    // require confirmation when rejecting contract
    for (let rejectLink of document.getElementsByClassName('reject_contract')) {
        let number = rejectLink.getAttribute('data-id');
        rejectLink.onclick = () => confirm('Wollen Sie den Vertrag Nr ' + number + ' wirklich ablehnen?');
    }
}

confirmVertraegeListe();

// used for mafia.inc.php
function setupMafiaAction() {
    // add handler for action selection
    let actionElement = document.getElementById('mafia_action');
    if (actionElement !== null) {
        actionElement.oninput = () => MafiaActionChange();
        MafiaActionChange();
    }
}

setupMafiaAction();

// used for index.php
function setupLinks() {
    let chefboxLink = document.getElementById('link_chefbox');
    if (chefboxLink !== null) {
        chefboxLink.onclick = () => ChefboxZeigen();
    }

    if (document.getElementById('link_logout') !== null) {
        let links = document.getElementById('Navigation').getElementsByTagName('a');
        for (let link of links) {
            if (link.classList.contains('inactive')) {
                link.href += '#Inhalt';
            }
        }
    }
}

setupLinks();

// used for bank.inc.php
function setupBank() {
    if (document.getElementById('form_bank') !== null) {
        // setup handlers
        document.getElementById('einzahlen').onchange = () => ChangeBankDepositWithdraw();
        document.getElementById('auszahlen').onchange = () => ChangeBankDepositWithdraw();
        if (document.getElementById('gruppen_kasse') !== null) {
            document.getElementById('gruppen_kasse').onchange = () => ChangeBankDepositWithdraw();
        }

        // select element
        ChangeBankDepositWithdraw();
    }
}

setupBank();

// used for all group pages
function setupLeaveGroup() {
    let leaveGroup = document.getElementById('leave_group');
    if (leaveGroup !== null) {
        leaveGroup.onclick = () => confirm('Wollen Sie wirklich aus der Gruppe austreten?');
    }
}

setupLeaveGroup();

// used for plantage.inc.php
function setupPlantage() {
    // calculate costs for hour based production
    let fastPlant = document.getElementById('fast_plant');
    let stundenElement = document.getElementById('stunden');
    stundenElement.onchange = () => RechneProduktionsKosten(
        1,
        fastPlant.getAttribute('data-cost-per-hour'),
        stundenElement.value,
        fastPlant.getAttribute('data-geld'),
        document.getElementById('pr_ko_all'), document.getElementById('plant_all')
    );
    stundenElement.onkeyup = stundenElement.onchange;

    // calculate costs for each ware
    for (let amountField of document.getElementsByClassName('amount_field')) {
        let id = amountField.getAttribute('data-id');
        amountField.onchange = () => RechneProduktionsKosten(
            amountField.getAttribute('data-menge'),
            amountField.getAttribute('data-kosten'),
            amountField.value,
            fastPlant.getAttribute('data-geld'),
            document.getElementById('pr_ko_' + id),
            document.getElementById('plant_' + id)
        );
        amountField.onkeyup = amountField.onchange;
    }

    // require confirmation when aborting production
    for (let deleteLink of document.getElementsByClassName('delete_plant_job')) {
        deleteLink.onclick = () => confirm('Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen die Kosten nicht zurück erstattet, lediglich die bisher produzierte Menge '
            + '(~ ' + deleteLink.getAttribute('data-refund') + ') wird Ihnen gut geschrieben.');
    }
}

if (document.getElementById('fast_plant') !== null) {
    setupPlantage();
}

let closeMessageBox = document.getElementById('close_message');
if (closeMessageBox !== null) {
    closeMessageBox.onclick = () => closeMessageBox.parentElement.remove();
}
