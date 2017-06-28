function getRandomPassword() {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < 5; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

function compareNumbers(a, b) {
    var aIsNumeric = $.isNumeric(a);
    var bIsNumeric = $.isNumeric(a);
    if (aIsNumeric && bIsNumeric) {
        return a - b;
    }

    // Compare non-number strings alphabetically
    if (! aIsNumeric && ! bIsNumeric) {
        return (a > b) ? 1 : -1;
    }

    // Place numeric values before non-numeric ones
    if (aIsNumeric && ! bIsNumeric) {
        return 1;
    }
    return -1;
}