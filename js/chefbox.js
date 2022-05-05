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
