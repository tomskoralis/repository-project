const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

// Change the icons inside the button based on previous settings
if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    themeToggleLightIcon.classList.remove('hidden');
} else {
    themeToggleDarkIcon.classList.remove('hidden');
}

const themeToggleBtn = document.getElementById('theme-toggle');
const themeToggleBtnText = themeToggleBtn.querySelector('span');

themeToggleBtn.addEventListener('click', function () {
    // toggle icons inside button
    themeToggleDarkIcon.classList.toggle('hidden');
    themeToggleLightIcon.classList.toggle('hidden');

    // if set via local storage previously
    if (localStorage.getItem('color-theme')) {
        if (localStorage.getItem('color-theme') === 'light') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
            themeToggleBtnText.textContent = 'Light Mode';
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
            themeToggleBtnText.textContent = 'Dark Mode';
        }

        // if NOT set via local storage previously
    } else {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
            themeToggleBtnText.textContent = 'Dark Mode';
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
            themeToggleBtnText.textContent = 'Light Mode';
        }
    }
});

function appendCurrencyUrl() {
    document.getElementById('searchForm').action =
        "/currency/" + document.getElementById('searchInput').value.toUpperCase();
}

function appendCurrencyUrlSmall() {
    document.getElementById('searchFormSmall').action =
        "/currency/" + document.getElementById('searchInputSmall').value.toUpperCase();
}

function appendUsersPageURl() {
    document.getElementById('userPagerForm').action =
        "/users/" + document.getElementById('pageInput').value;
}

function appendUsersSearchUrl() {
    document.getElementById('usersSearchForm').action =
        "/search/" + document.getElementById('userSearchInput').value;
}

function appendUsersSearchPageURl() {
    document.getElementById('userPagerForm').action =
        "/search/" + location.pathname.split("/")[2] + "/" +
        document.getElementsByName('pageInput')[0].value;
}