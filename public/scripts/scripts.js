function appendCurrencyUrl() {
    document.getElementById('searchForm').action =
        "/currency/" + document.getElementsByName('search')[0].value;
}