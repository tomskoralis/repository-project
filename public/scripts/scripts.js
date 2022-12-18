function appendCurrencyUrl() {
    document.getElementById('searchForm').action =
        "/currency/" + document.getElementsByName('searchInput')[0].value;
}

function appendUsersPageURl() {
    document.getElementById('userPagerForm').action =
        "/users/" + document.getElementsByName('pageInput')[0].value;
}

function appendUsersSearchUrl() {
    document.getElementById('usersSearchForm').action =
        "/search/" + document.getElementsByName('userSearchQuery')[0].value;
}

function appendUsersSearchPageURl() {
    document.getElementById('userPagerForm').action =
        "/search/" + location.pathname.split("/")[2] + "/" +
        document.getElementsByName('pageInput')[0].value;
}