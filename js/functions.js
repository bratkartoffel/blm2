function ChefboxZeigen(seite) {
    const chefboxPopup = window.open(seite, 'chefbox', 'height=690,width=240,scrollbars=yes,resizable=yes');
    chefboxPopup.focus();
    return false;
}

function BLMzeigen(link) {
    if (opener) {
        opener.focus();
    } else {
        const blm = window.open(link, 'blm', 'fullscreen=yes,location=yes,resizable=yes,menubar=yes,scrollbars=yes,status=yes,toolbar=yes');
        blm.focus();
    }
    return false;
}

function BLMEnde() {
    if (opener) {
        opener.focus();
        self.close();
    } else {
        document.location.href = "/actions/logout.php?popup=1";
    }
    return false;
}

function BLMNavigation(link) {
    if (opener) {
        opener.document.location.href = link;
        opener.focus();
    } else {
        BLMzeigen(link);
    }
    return false;
}

function ZeichenUebrig(Feld, Text) {
    Text.innerText = 4096 - Feld.value.length;
    return true;
}

function RechneProduktionsKosten(BasisMenge, BasisPreis, Menge, Geld, TextFeld, Button) {
    const kosten = Menge * (BasisPreis / BasisMenge);

    TextFeld.innerText = "Kosten: " + kosten.toLocaleString('de-DE', {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    }) + " €";

    if (kosten > Geld) {
        Button.enabled = "";
        Button.disabled = "disabled";
    } else {
        Button.disabled = "";
        Button.enabled = "enabled";
    }
}

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

function submit(button) {
    button.form.submit();
    button.disabled = 'disabled';
    button.value = 'Bitte warten...';
    return false;
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
            field.innerText = "";
            let date = new Date(value + direction * 1000);
            if (value > 86400000 && direction < 0) {
                field.innerText += Math.floor(value / 86400000) + " Tage ";
            }
            field.innerText += date.toLocaleTimeString("de-DE", {
                hour12: false,
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
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

function MarkActiveLink() {
    let params = new URLSearchParams(window.location.search);
    let page;
    if (params.has('p')) {
        page = params.get('p');
    } else {
        page = 'index';
    }
    if (page.includes('_')) {
        page = page.split('_')[0];
    }
    Array.prototype.forEach.call(document.getElementById("Navigation").getElementsByTagName("a"), (field) => {
        if (field.href.includes(page)) {
            field.innerHTML = '→ <i>' + field.innerHTML + "</i>";
            field.style.color = "#555555";
        }
    });
}

function MafiaActionChange() {
    let data = mafia_cost_data[Number.parseInt(document.getElementById('action').value)];
    let texts = [
        data[0]['cost'] + ' € / ' + (100 * data[0]['chance']) + '%',
        data[1]['cost'] + ' € / ' + (100 * data[1]['chance']) + '%',
        data[2]['cost'] + ' € / ' + (100 * data[2]['chance']) + '%',
        data[3]['cost'] + ' € / ' + (100 * data[3]['chance']) + '%',
    ];

    let options = document.getElementById('level').getElementsByTagName('option');
    for (let i = 0; i < options.length; i++) {
        options[i].innerText = texts[i];
    }
}

// used in nachrichten_schreiben.inc.php
// noinspection JSUnusedGlobalSymbols
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

let reloadOnCountdown = false;
window.setInterval(CountdownFields, 1000);
