let loginFunction = {
    init: function (formElement) {

    }
}

document.addEventListener("DOMContentLoaded", function () {
    let loginForm = document.getElementById('login_form');
    if (loginForm) {
        loginFunction.init(loginForm);
    }
});
